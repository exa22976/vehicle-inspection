<!-- resources/views/emails/inspection-request.blade.php -->
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>車両点検のお願い</title>
</head>

<body style="font-family: sans-serif; color: #333;">
    <p>{{ $user->name }} 様</p>
    <p>お疲れ様です。</p>
    <p>{{ \Carbon\Carbon::parse($record->inspectionRequest->target_week_start)->format('Y年n月j日') }}週の車両点検のお願いです。</p>
    <p>
        以下のURLにアクセスし、担当車両の点検報告をお願いいたします。<br>
        ※このURLの有効期限は7日間です。
    </p>
    <p style="margin: 20px 0;">
        <a href="{{ route('inspection.form', ['token' => $record->one_time_token]) }}"
            style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 5px;">
            点検フォームへアクセス
        </a>
    </p>
    @if ($record->inspectionRequest->remarks)
    <hr>
    <p><strong>管理者からの申し送り事項:</strong></p>
    <p style="white-space: pre-wrap;">{{ $record->inspectionRequest->remarks }}</p>
    <hr>
    @endif
    <p>よろしくお願いいたします。</p>
</body>

</html>