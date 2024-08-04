<?php

namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;

use File;
use Validator;
use App\blog_article;
use App\BlogComment;

use Illuminate\Http\Request;

class BlogArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($pageNumber)
    {
        $blogs = blog_article::where('blog_articles.status', 'approved')
        ->skip($pageNumber * 8)->limit(8)->with('category')->orderBy('published_at', 'desc')->get();
        return FacadeResponse::json($blogs);
        
    }
    public function getArticleList ($page, $size) {
    
        $blogs = blog_article::where('blog_articles.status', 'approved')
        ->select('id',  'name', 'email', 'category_id', 'titile as title', 'short_description', 'image', 'status', 'is_boosted', 'published_at', 'created_at', 'updated_at')
        ->skip($page * $size)->limit($size)->with('category')->orderBy('published_at', 'desc')->get();
        $totalBlog = blog_article::where('blog_articles.status', 'approved')->count();
        $obj = (Object) [
            "total_page" => ceil($totalBlog / $size),
            "records" => $blogs
            ];
        return FacadeResponse::json($obj);
    }
    public function details($id)
    {
        $details = blog_article::where('id', $id)->with('category', 'comments')
        ->first();
        $recentPost = blog_article::orderBy('published_at', 'desc')->limit(2)->get();
        $details->recents = $recentPost;
   
        
        
        $popularPost = blog_article::orderBy('published_at', 'desc')->where('is_boosted', 1)->limit(2)->get();
        $details->populars = $popularPost;
        
        
        
        
        return FacadeResponse::json($details);
        
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store (Request $request) {


        $categoryId = null;
        $response = new ResponseObject;
        
            
            
            
        $formData = json_decode($request->data, true);
        // $data = $request->json()->all();

        $validator = Validator::make($formData, [
            'title' => 'required',
            'description' => 'required'
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors()->first();
            // $response->messages = "Validation failed";
            return FacadeResponse::json($response);
        }
      
        $categoryId = 1;
        if ($request->hasFile('image') ) {
            $image_info = getimagesize($request->file('image'));
            if ($image_info[0] > 600 || $image_info[1] > 400) {
                $response->status = $response::status_fail;
                $response->messages = "Image dimension should not be more than 600 X 400";
                return FacadeResponse::json($response);
            }
            // print_r($image_info);
            $destinationPath = 'uploads/blogs';
            if(!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, $mode = 0777, true, true);
            }
            

            $image = $request->file('image');
            $formData['image'] = 'blog_'.time().'.'.$image->getClientOriginalExtension();
            $image->move($destinationPath,$formData['image']);
            $this->emailNotification($formData);
            $blog = blog_article::create([
                'user_id' => 7,
                'category_id' => $categoryId,
                'name' => $formData['name'],
                'email' => $formData['email'],
                'titile' => $formData['title'],
                'description' => $formData['description'],
                'status' => 'pending',
                'image' => 'https://api.bacbonschool.com/'.$destinationPath.'/'.$formData['image']
            ]);

            $response->status = $response::status_ok;
            $response->messages = "Your article has been submitted. Please, allow some time to get approval. Once your article gets approved, it will show up in the blog section.";
            $response->result = $blog;
            return FacadeResponse::json($response);
        } else {
            $response->status = $response::status_fail;
            $response->messages = "Please select an image";
            return FacadeResponse::json($response);
        }

    }
    /**
     * Display the specified resource.
     *
     * @param  \App\blog_article  $blog_article
     * @return \Illuminate\Http\Response
     */
    public function show(blog_article $blog_article)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\blog_article  $blog_article
     * @return \Illuminate\Http\Response
     */
    public function edit(blog_article $blog_article)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\blog_article  $blog_article
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, blog_article $blog_article)
    {
        //
    }


    public function emailNotification($data) {
        // Recipient
        $toEmail = 'mehedirueen@gmail.com';

        // Sender
        $from = $data['email'] ? $data['email'] : 'rueen@bacbonltd.com';
        $fromName = $data['name'] ;

        // Subject
        $emailSubject = 'New Article to Blog';

        $htmlContent = '<html><body>';
        $htmlContent .= '<h2 style="background: #1d72ba; color: #fff; padding: 5px;">New article has been submitted.</h2>';
        $htmlContent .= '<p><b>Name:</b> ' .  $data['name'] . '</p>';
        $htmlContent .= '<p><b>Email:</b> ' . $data['email'] . '</p>';
        $htmlContent .= '<p><b>Title:</b> ' . $data['title'] . '</p>';
        $htmlContent .= '<p><b>Content:</b> ' . $data['description'] . '</p>';

        $htmlContent .= '</body></html>';


        $headers = "From: $fromName" . " <" . $from . ">";
        $headers .= "\r\n" . "MIME-Version: 1.0";
        $headers .= "\r\n" . "Content-Type: text/html; charset=ISO-8859-1";
        $headers .= "Reply-To: ". $from. "\r\n";
        $headers .= "Return-Path: ". $from. "\r\n";

        return mail($toEmail, $emailSubject, $htmlContent, $headers);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\blog_article  $blog_article
     * @return \Illuminate\Http\Response
     */
    public function destroy(blog_article $blog_article)
    {
        //
    }
}
