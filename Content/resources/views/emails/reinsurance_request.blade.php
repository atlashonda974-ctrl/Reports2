<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }
        .email-header {
            background: #007bff;
            color: #fff;
            padding: 20px;
            text-align: center;
        }
        .email-body {
            padding: 20px;
        }
        .email-footer {
            background: #f8f9fa;
            padding: 15px 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        .pre-formatted {
            white-space: pre-line;
            font-family: inherit;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h2 style="margin: 0;">Atlas Insurance Ltd.</h2>
            <p style="margin: 10px 0 0 0;">Reinsurance Request Note</p>
        </div>
        
        <div class="email-body">
            <div class="pre-formatted">{{ $content }}</div>
        </div>
        
        <div class="email-footer">
            <p><strong>Atlas Insurance Ltd.</strong></p>
            <p>This is an automated email. Please do not reply to this email address.</p>
        </div>
    </div>
</body>
</html>