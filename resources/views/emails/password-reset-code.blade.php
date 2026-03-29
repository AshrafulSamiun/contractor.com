<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DeskDrop Password Reset Code</title>
  </head>
  <body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:24px 12px;">
      <tr>
        <td align="center">
          <table role="presentation" width="560" cellspacing="0" cellpadding="0" style="background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e2e8f0;">
            <tr>
              <td style="padding:22px 26px;background:#1d4ed8;color:#ffffff;">
                <div style="font-size:18px;font-weight:700;">DeskDrop</div>
                <div style="opacity:0.9;font-size:12px;margin-top:6px;">Password Recovery</div>
              </td>
            </tr>
            <tr>
              <td style="padding:24px 26px 10px;">
                <div style="font-size:16px;font-weight:600;">Hi {{ $name ?? 'there' }},</div>
                <p style="margin:10px 0 0;font-size:14px;line-height:1.7;color:#334155;">
                  Use this code to reset your DeskDrop password.
                </p>
              </td>
            </tr>
            <tr>
              <td align="center" style="padding:12px 26px 20px;">
                <div style="display:inline-block;padding:14px 24px;border-radius:12px;background:#e2e8f0;color:#0f172a;font-size:30px;font-weight:700;letter-spacing:7px;">
                  {{ $code }}
                </div>
                <div style="margin-top:12px;font-size:12px;color:#64748b;">
                  Code expires in {{ $expires_minutes ?? 10 }} minutes.
                </div>
              </td>
            </tr>
            <tr>
              <td style="padding:0 26px 24px;">
                <p style="margin:0;font-size:13px;line-height:1.6;color:#64748b;">
                  If you did not request a password reset, you can ignore this message.
                </p>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>
