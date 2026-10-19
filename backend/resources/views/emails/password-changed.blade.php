<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your LittleStep Password Was Changed</title>
</head>
<body style="margin:0;padding:32px 12px;background-color:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background-color:#ffffff;border-radius:12px;">
                    <tr>
                        <td style="padding:32px;">
                            <h1 style="margin:0 0 16px;font-size:24px;line-height:1.3;color:#111827;">Your password was changed</h1>
                            <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#374151;">
                                The password for your LittleStep teacher account was changed on {{ $changedAt }}.
                                Other devices that were signed in to your account have been signed out.
                            </p>
                            <p style="margin:0 0 24px;font-size:16px;line-height:1.6;color:#374151;">
                                If you made this change, no action is needed.
                            </p>
                            <p style="margin:0 0 16px;font-size:16px;line-height:1.6;color:#374151;">
                                <strong>Didn't change your password?</strong> Reset it right away to secure your account.
                            </p>
                            <p style="margin:0 0 24px;">
                                <a href="{{ $resetUrl }}" rel="noopener noreferrer" referrerpolicy="no-referrer" style="display:inline-block;padding:12px 24px;background-color:#111827;color:#ffffff;text-decoration:none;font-weight:bold;border-radius:8px;">
                                    Reset Password
                                </a>
                            </p>
                            <p style="margin:0;font-size:14px;line-height:1.6;color:#6b7280;">
                                For your security, this email never includes your password.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
