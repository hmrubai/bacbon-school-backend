<?php

namespace App\Imports;
use DB;
use App\Lamp;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class UsersImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        $data = [];

        foreach ($rows as $row)
        {
            $user = DB::table('users')->where('mobile_number', 'like', '%' . $row[4] . '%')->first();
            if($user) {
                $data[] = array(

                'user_id' => $user->id,
                // 'name'     => $row[0],
                // 'mobile_number'    => $row[1],
                // 'email'    => $row[2],
                // 'gender'    => $row[3],
                'age'    => $row[3],
                'passport'    => $row[5] == 'Yes' ? true : false,
                'organization'    => $row[1],
                'reason'    => $row[6],
                'background'    => $row[7],
                'remark'    => $row[8],
                'contributionProcess'    => $row[9],
                'created_at' => date('Y-m-d H:i:s')
                );

            } else {
                dd($row[4]);
                return $row[4];
            }

        }

        Lamp::insert($data);
    }
}

