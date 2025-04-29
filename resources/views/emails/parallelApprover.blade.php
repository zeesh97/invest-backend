<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Approval Status Notification</title>
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
        <h1>Parallel approver of {{ $status['name'] }} for {{ $status['form_name'] }} form.</h1>
        <?php $slug = str_replace(' ', '-', strtolower($status['form_name'])); ?>
        <p>This email notifies you about that you are the parallel approver of {{ $status['name'] }} for the
            "{{ $status['form_name'] }} form".</p>
            <table style="border-collapse: collapse; width: 100%;">
                <tbody>
                    <tr>
                        <th style="text-align: left; padding: 5px 10px;">Approve this for</th>
                        <td style="padding: 5px 10px;">{{ $status['name'] }}</td>
                    </tr>
                    <tr>
                        <th style="text-align: left; padding: 5px 10px;">Email</th>
                        <td style="padding: 5px 10px;">{{ $status['email'] }}</td>
                    </tr>
                    <tr>
                        <th style="text-align: left; padding: 5px 10px;">Employee ID</th>
                        <td style="padding: 5px 10px;">{{ $status['employee_no'] }}</td>
                    </tr>
                    <tr>
                        <th style="text-align: left; padding: 5px 10px;">Status</th>
                        <td style="padding: 5px 10px; text-transform: capitalize; text-decoration: bold;">{{ $status['status'] }}
                        </td>
                    </tr>
                </tbody>
            </table>

        <p>Click the button below to view the details of the form:</p>
        <a href="<?php echo config('app.frontend_url') . '/' .$status['slug']. '/details/' . $status['key']; ?>" class="button">View Details</a>

        <p>Thanks,<br><?php echo config('app.name'); ?></p>
    </div>
</body>

</html>


