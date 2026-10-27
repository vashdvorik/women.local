<!DOCTYPE html>
<html lang="{{ $locale }}">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="margin:0;background:#FAF4F1;font-family:Arial,Helvetica,sans-serif;color:#21140F;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FAF4F1;padding:32px 16px;">
    <tr><td align="center">
        <table role="presentation" width="520" cellpadding="0" cellspacing="0" style="max-width:520px;width:100%;background:#FFFDFC;border:1px solid #E9DCD5;border-radius:20px;">
            <tr><td style="padding:32px 32px 8px;font-family:Georgia,'Times New Roman',serif;font-size:24px;color:#2A1913;">Women’s hub</td></tr>
            <tr><td style="padding:8px 32px 0;font-size:16px;line-height:1.55;">{{ __('participant.mail.greeting', [], $locale) }}</td></tr>
            <tr><td style="padding:12px 32px 0;font-size:16px;line-height:1.55;">{{ $line }}</td></tr>
            <tr><td style="padding:24px 32px;">
                <a href="{{ $url }}" style="display:inline-block;background:#2A1913;color:#FFFFFF;text-decoration:none;font-weight:bold;font-size:15px;padding:14px 28px;border-radius:999px;">{{ $button }}</a>
            </td></tr>
            <tr><td style="padding:0 32px 8px;font-size:13px;line-height:1.5;color:#756158;">{{ $note }}</td></tr>
            <tr><td style="padding:0 32px 24px;font-size:12px;line-height:1.5;color:#8C776E;word-break:break-all;">{{ $url }}</td></tr>
            <tr><td style="padding:16px 32px 28px;border-top:1px solid #E9DCD5;font-size:12px;color:#8C776E;">{{ __('participant.mail.footer', [], $locale) }}</td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
