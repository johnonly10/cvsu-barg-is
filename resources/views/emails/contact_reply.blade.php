<style>
    body {
        font-family: Arial, sans-serif;
        padding: 20px;
        background-color: #f8f8f8;
        color: #333;
    }
    .email-container {
        background-color: #fff;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        width: 600px;
        margin: auto;
    }
    h1 {
        color: #4CAF50;
    }
    blockquote {
        margin: 10px 0;
        padding-left: 20px;
        border-left: 3px solid #4CAF50;
    }
    p {
        font-size: 14px;
        line-height: 1.6;
    }
</style>

<div class="email-container">
    <h1>Response to Your Inquiry</h1>
    <p>Hello {{ $contactName }},</p>
    <p>Thank you for reaching out to us. Here's our reply:</p>
    <blockquote>Your Inquiry: {{ $contactMessage }}</blockquote>
    <p><strong>Admin's Reply:</strong></p>
    <blockquote>{{ $replyMessage }}</blockquote>
    <p>Thank you again for your patience!</p>
    <p>Best regards,<br>{{ config('app.name') }}</p>
</div>

