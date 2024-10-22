<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>Quiz Result - {{ $student_name }}</title>
    <style>
        @font-face {
            font-family: 'SolaimanLipi';
            src: url({{ public_path('fonts/SolaimanLipi.ttf') }}) format('truetype');
        }
        body {
            font-family: 'SolaimanLipi', sans-serif;
            /* font-family: 'Verdana', 'Arial', sans-serif;  */
        }
        .mcq-container {
            width: 80%;
            margin: 0 auto;
        }
        .question {
            margin-bottom: 20px;
        }
        .options {
            list-style-type: none;
            padding: 0;
        }
        .option {
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .correct {
            background-color: #d4edda;
            color: #155724;
        }
        .incorrect {
            background-color: #f8d7da;
            color: #d30d20;
        }
        .neutral {
            color: #000;
        }
        .student-choice {
            font-weight: bold;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse; /* Collapse borders for cleaner look */
            margin-bottom: 20px; /* Spacing below the table */
        }
        .summary-table th, .summary-table td {
            border: 1px solid #000; /* Border for table cells */
            padding: 8px; /* Padding inside cells */
            text-align: left; /* Align text to the left */
        }
        .summary-table th {
            background-color: #f2f2f2; /* Light gray background for header */
        }

        .explanation {
            font-style: italic; /* Makes the explanation text italic */
            color: #555; /* Gray color for explanation text */
            margin-top: 5px; /* Spacing above explanation text */
        }

    </style>
</head>
<body>

    <div class="mcq-container">
        
        <h2>MCQ Result Summary</h2>
        <table class="summary-table">
            <tbody>
                <tr>
                    <td>Name</td>
                    <td><strong>{{ $student_name }}</strong></td>
                </tr>
                <tr>
                    <td>Phone No.</td>
                    <td>{{ $mobile }}</td>
                </tr>
                <tr>
                    <td>Number Of Questions</td>
                    <td>{{ $total_questions }}</td>
                </tr>total_marks
                <tr>
                    <td>Obtained Marks in MCQ</td>
                    <td>{{ $total_marks }}</td>
                </tr>
                <tr>
                    <td>Submission Status</td>
                    <td>{{ $result_status }}</td>
                </tr>
                
            </tbody>
        </table>

        @php $i = 1; @endphp
        @foreach($result as $item)
            <div class="question">
                <p><strong>{{ $i++ }}. {{ $item->question }}</strong></p>
                <ul class="options">

                    @if($item->answer != null && $item->correct_answer != null)
                        <li class="option correct" >A. {{ $item->option1 }} <span class="student-choice">(Student's Choice)</span></li>
                    @elseif($item->answer != null && $item->correct_answer == null)
                        <li class="option incorrect">A. {{ $item->option1 }} <span class="student-choice">(Student's Choice)</span></li>
                    @elseif($item->answer == null && $item->correct_answer != null)
                        <li class="option correct">A. {{ $item->option1 }} <span class="student-choice">(Correct Choice)</span></li>
                    @else
                        <li class="option">A. {{ $item->option1 }} <span class="student-choice"></span></li>
                    @endif

                    @if($item->answer2 != null && $item->correct_answer2 != null)
                        <li class="option correct">B. {{ $item->option2 }} <span class="student-choice">(Student's Choice)</span></li>
                    @elseif($item->answer2 != null && $item->correct_answer2 == null)
                        <li class="option incorrect">B. {{ $item->option2 }} <span class="student-choice">(Student's Choice)</span></li>
                    @elseif($item->answer2 == null && $item->correct_answer2 != null)
                        <li class="option correct">B. {{ $item->option2 }} <span class="student-choice">(Correct Choice)</span></li>
                    @else
                        <li class="option">B. {{ $item->option2 }} <span class="student-choice"></span></li>
                    @endif

                    @if($item->answer3 != null && $item->correct_answer3 != null)
                        <li class="option correct">C. {{ $item->option3 }} <span class="student-choice">(Student's Choice)</span></li>
                    @elseif($item->answer3 != null && $item->correct_answer3 == null)
                        <li class="option incorrect">C. {{ $item->option3 }} <span class="student-choice">(Student's Choice)</span></li>
                    @elseif($item->answer3 == null && $item->correct_answer3 != null)
                        <li class="option correct">C. {{ $item->option3 }} <span class="student-choice">(Correct Choice)</span></li>
                    @else
                        <li class="option">C. {{ $item->option3 }} <span class="student-choice"></span></li>
                    @endif

                    @if($item->answer4 != null && $item->correct_answer4 != null)
                        <li class="option correct">D. {{ $item->option4 }} <span class="student-choice">(Student's Choice)</span></li>
                    @elseif($item->answer4 != null && $item->correct_answer4 == null)
                        <li class="option incorrect">D. {{ $item->option4 }} <span class="student-choice">(Student's Choice)</span></li>
                    @elseif($item->answer4 == null && $item->correct_answer4 != null)
                        <li class="option correct">D. {{ $item->option4 }} <span class="student-choice">(Correct Choice)</span></li>
                    @else
                        <li class="option">D. {{ $item->option4 }} <span class="student-choice"></span></li>
                    @endif

                    <p class="explanation"> <strong>Explanation: </strong>  {{ $item->explanation_text }}</p>

                </ul>
            </div>
        @endforeach

    </div>

</body>
</html>