<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contractor.com Verification Code</title>
  </head>
  <body style="margin:0;padding:0;background:#0b1220;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#0b1220;padding:32px 12px;">
      <tr>
        <td align="center">
          <table role="presentation" width="560" cellspacing="0" cellpadding="0" style="background:#ffffff;border-radius:18px;overflow:hidden;box-shadow:0 18px 50px rgba(2,6,23,0.35);">
            <tr>
              <td style="background:linear-gradient(135deg,#0f1e3a,#1e40af);padding:26px 30px;color:#ffffff;">
                <div style="font-size:18px;font-weight:700;letter-spacing:0.5px;">Contractor.com</div>
                <div style="font-size:12px;opacity:0.9;margin-top:6px;text-transform:uppercase;letter-spacing:1.6px;">Verification</div>
              </td>
            </tr>
            <tr>
              <td style="padding:30px 30px 8px;">
                <div style="font-size:16px;font-weight:600;margin-bottom:8px;color:#0f172a;">Hi {{ $name ?? 'there' }},</div>
                <div style="font-size:14px;line-height:1.7;color:#475569;">
                  Here is your one-time verification code to complete your Contractor.com sign-in.
                </div>
              </td>
            </tr>
            <tr>
              <td align="center" style="padding:16px 30px 26px;">
                <div style="display:inline-block;padding:18px 32px;border-radius:14px;background:#e0f2fe;color:#0f1e3a;font-size:28px;font-weight:700;letter-spacing:8px;border:1px solid #bfdbfe;">
                  {{ $code }}
                </div>
                <div style="margin-top:12px;font-size:12px;color:#64748b;">
                  Expires in {{ $expires_minutes ?? 10 }} minutes.
                </div>
              </td>
            </tr>
            @if(!empty($verification_url))
            <tr>
              <td align="center" style="padding:0 30px 20px;">
                <a href="{{ $verification_url }}" style="display:inline-block;background:#1d4ed8;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:10px;font-size:14px;font-weight:600;">
                  Verify Email Instantly
                </a>
                <div style="margin-top:10px;font-size:12px;color:#64748b;">
                  This secure link expires in {{ $verification_link_expires_minutes ?? 60 }} minutes.
                </div>
              </td>
            </tr>
            @endif
            <tr>
              <td style="padding:0 30px 26px;">
                <div style="font-size:13px;line-height:1.6;color:#64748b;">
                  If you did not request this code, please ignore this message or contact support.
                </div>
              </td>
            </tr>
            <tr>
              <td style="background:#0f172a;padding:16px 30px;font-size:12px;color:#cbd5f5;">
                Contractor.com Security - Never share this code.
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>
