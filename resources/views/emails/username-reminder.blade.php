<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contractor.com Username Reminder</title>
  </head>
  <body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:24px 12px;">
      <tr>
        <td align="center">
          <table role="presentation" width="560" cellspacing="0" cellpadding="0" style="background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e2e8f0;">
            <tr>
              <td style="padding:22px 26px;background:#1d4ed8;color:#ffffff;">
                <div style="font-size:18px;font-weight:700;">Contractor.com</div>
                <div style="opacity:0.9;font-size:12px;margin-top:6px;">Username Reminder</div>
              </td>
            </tr>
            <tr>
              <td style="padding:24px 26px 12px;">
                <div style="font-size:16px;font-weight:600;">Hi {{ $name ?? 'there' }},</div>
                <p style="margin:10px 0 0;font-size:14px;line-height:1.7;color:#334155;">
                  Here are your sign-in details for Contractor.com.
                </p>
              </td>
            </tr>
            <tr>
              <td style="padding:0 26px 20px;">
                <div style="border:1px solid #dbeafe;background:#eff6ff;border-radius:12px;padding:14px 16px;">
                  <div style="font-size:13px;color:#475569;">Username</div>
                  <div style="font-size:18px;font-weight:700;color:#0f172a;margin-top:4px;">
                    {{ $username ?: 'No username configured. Use your email to sign in.' }}
                  </div>
                  <div style="font-size:13px;color:#475569;margin-top:10px;">Email: {{ $email }}</div>
                </div>
              </td>
            </tr>
            <tr>
              <td style="padding:0 26px 24px;">
                <a href="{{ $login_url }}" style="display:inline-block;background:#1d4ed8;color:#ffffff;text-decoration:none;padding:11px 20px;border-radius:10px;font-size:14px;font-weight:600;">
                  Go to Sign In
                </a>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>
