<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Account Logs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Account Logs</h1>
    <table>
        <thead>
            <tr>
                <th>User</th>
                <th>Email</th>
                <th>Login Time</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($logs as $log)
                <tr>
                    <td>{{ $log->user->firstname }} {{ $log->user->lastname }}</td>
                    <td>{{ $log->user->email }}</td>
                    <td>{{ $log->login_time }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
