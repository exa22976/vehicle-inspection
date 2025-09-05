<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>週次点検進捗レポート</title>
    <style>
        body {
            font-family: sans-serif;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .header {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
        }

        .card {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .progress-bar {
            background-color: #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
            height: 20px;
        }

        .progress {
            background-color: #4caf50;
            height: 100%;
            text-align: center;
            color: white;
            line-height: 20px;
        }

        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .stats-table td {
            padding: 8px;
            border-top: 1px solid #ddd;
        }

        .button {
            display: inline-block;
            background-color: #007bff;
            color: #ffffff;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">週次点検進捗レポート</div>
        <p>先週 ({{ $inspectionRequest->target_week_start }} 開始週) の点検結果の最終集計です。</p>

        <div class="card">
            <h3>進捗率: {{ $stats['progress_rate'] }}%</h3>
            <div class="progress-bar">
                <div class="progress" style="width: {{ $stats['progress_rate'] }}%;">
                    {{ $stats['completed'] }} / {{ $stats['total'] }}
                </div>
            </div>
            <table class="stats-table">
                <tr>
                    <td>正常</td>
                    <td style="text-align: right;">{{ $stats['results']['正常'] }} 件</td>
                </tr>
                <tr>
                    <td>要確認</td>
                    <td style="text-align: right;">{{ $stats['results']['要確認'] }} 件</td>
                </tr>
                <tr>
                    <td>異常</td>
                    <td style="text-align: right;">{{ $stats['results']['異常'] }} 件</td>
                </tr>
            </table>
        </div>

        <a href="{{ config('app.url') . '/admin/dashboard?week=' . $inspectionRequest->target_week_start }}" class="button">
            ダッシュボードで詳細を確認
        </a>
    </div>
</body>

</html>