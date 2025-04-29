<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Assigned Task Notification</title>
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

        th, td {
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
            background-color: #4CAF50; /* Green */
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Assigned Task for <?php echo $status['form_name']; ?></h1>
        <p>This email notifies you about the assigned task to you and your team for the form "<?php echo $status['form_name']; ?>".</p>

        <table>
            <tbody>
                <tr>
                    <th>Initiator Name</th>
                    <td style="text-transform: capitalize; font-weight: bold;"><?php echo $status['initiator_name']; ?></td>
                </tr>
            </tbody>
        </table>

        <p>Click the button below to view the details of the form:</p>
        <a href="<?php echo config('app.frontend_url') . '/' .$status['slug']. '/details/' . $status['key']; ?>" class="button">View Details</a>

        <p>Thanks,<br><?php echo config('app.name'); ?></p>
    </div>
</body>
</html>
