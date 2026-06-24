<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Restablecer contraseña - GymGest</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f4f4f4; padding: 20px; border-radius: 5px;">
        <h1 style="color: #2563eb;">Restablecer contraseña</h1>
        <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en GymGest.</p>

        <p>Para crear una nueva contraseña, haz clic en el botón de abajo:</p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $resetUrl }}"
               style="background-color: #2563eb; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
                Restablecer Contraseña
            </a>
        </div>

        <p>Si no solicitaste restablecer tu contraseña, puedes ignorar este email. Tu contraseña no cambiará.</p>

        <p style="font-size: 0.9em; color: #666; margin-top: 30px;">
            Este enlace expirará en 60 minutos.
        </p>

        <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">

        <p style="font-size: 0.8em; color: #999;">
            Si tienes problemas al hacer clic en el botón, copia y pega la siguiente URL en tu navegador:<br>
            <span style="color: #2563eb; word-break: break-all;">{{ $resetUrl }}</span>
        </p>
    </div>
</body>
</html>
