<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bienvenido a Viejocracia</title>
</head>
<body style="margin:0;padding:0;background:#F4F7FB;font-family:Arial,Helvetica,sans-serif;color:#1F2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#F4F7FB;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width:600px;width:100%;background:#ffffff;border:1px solid rgba(11,61,145,0.12);">
                    <tr>
                        <td style="background:#0B3D91;padding:28px 32px;">
                            <p style="margin:0;color:#00B3A4;font-size:12px;letter-spacing:0.16em;text-transform:uppercase;">Soberanía Creativa Responsable</p>
                            <h1 style="margin:8px 0 0;color:#ffffff;font-size:32px;letter-spacing:0.04em;">Viejo<span style="color:#FF8A00;">cracia</span></h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px;font-size:16px;line-height:1.6;">Hola, <strong>{{ $nick }}</strong>.</p>
                            <p style="margin:0 0 16px;font-size:16px;line-height:1.6;">
                                Ya estás dentro. Desde <strong>{{ $department }}</strong> tu voz cuenta: el poder no es una herencia, es un préstamo del pueblo.
                            </p>
                            <p style="margin:0 0 24px;font-size:16px;line-height:1.6;">
                                Viejocracia es el lugar para discutir, firmar y proponer la agenda. Nadie debería esperar veinte años para tener voz.
                            </p>
                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin:0 0 24px;">
                                <tr>
                                    <td style="background:#0096D6;">
                                        <a href="{{ $homeUrl }}" style="display:inline-block;padding:12px 22px;color:#ffffff;text-decoration:none;font-weight:700;">Entrar a la plataforma</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:0 0 8px;font-size:15px;line-height:1.6;"><a href="{{ $debatesUrl }}" style="color:#0B3D91;">Debatir</a> — argumentos, no consigna vacía.</p>
                            <p style="margin:0 0 8px;font-size:15px;line-height:1.6;"><a href="{{ $votesUrl }}" style="color:#0B3D91;">Firmar</a> — campañas abiertas, una firma por persona.</p>
                            <p style="margin:0 0 24px;font-size:15px;line-height:1.6;"><a href="{{ $suggestUrl }}" style="color:#0B3D91;">Sugerir un tema</a> — el próximo debate o votación puede ser el tuyo.</p>
                            <p style="margin:0;font-size:14px;line-height:1.6;color:#6B7280;">
                                Si no creaste esta cuenta, podés ignorar este correo.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#0B3D91;padding:18px 32px;color:#ffffff;font-size:12px;">
                            Viejocracia · Participación ciudadana · Bolivia
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
