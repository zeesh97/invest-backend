<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>QA Request Notification</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            padding: 20px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        p {
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            /* Green */
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Quality Assurance Request for <?php echo $status['form_name']; ?></h1>
        <p>Dear Sir / Madam</p>

        <p>We are pleased to inform you that the work on <strong><?php echo $status['form_name'].'</strong> - <strong>'.$status['request_title']; ?> </strong> has been completed.
            Your feedback is highly valuable to us.</p>

        <p>Please click the button below to submit your feedback.</p>

        <a href="{{ config('app.frontend_url') . '/get-qa-assigned/add?detail='.$status['params'] }}"
            class="button">Feedback</a>

        <p>Thank you for your time and input.</p>


        <p>Best regards, <br><?php echo config('app.name'); ?></p>
    </div>
</body>

</html>
