<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Votre licence 14TRADES PRO</title>
</head>
<body>
<div style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif;">
    <div style="background: linear-gradient(135deg, #0B1220, #1A237E); color: white; padding: 30px; text-align: center;">
        <h1 style="margin: 0; color: #FFD700;">14TRADES PRO</h1>
        <p style="margin: 10px 0 0; opacity: 0.8;">Votre licence a été créée avec succès</p>
    </div>

    <div style="padding: 30px; background: #f8f9fa;">
        <h2 style="color: #1A237E;">Bonjour {{ $user->name }},</h2>

        <p>Votre licence 14TRADES PRO a été créée avec les informations suivantes :</p>

        <div style="background: white; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <table style="width: 100%;">
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Compte MT5 :</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{{ $license->mt5_account }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Serveur :</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{{ $license->server }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Plan :</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;">
                            <span style="background: {{ $license->plan == 'elite' ? '#e74c3c' : ($license->plan == 'normal' ? '#f39c12' : '#3498db') }}; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px;">
                                {{ strtoupper($license->plan) }}
                            </span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Expiration :</strong></td>
                    <td style="padding: 8px 0;">{{ $license->expires_at->format('d/m/Y') }}</td>
                </tr>
            </table>
        </div>

        <h3 style="color: #1A237E;">Instructions d'installation :</h3>
        <ol style="padding-left: 20px;">
            <li>Téléchargez l'EA depuis votre espace client</li>
            <li>Décompressez le fichier dans le dossier Experts de MT5</li>
            <li>Redémarrez MT5</li>
            <li>Faites glisser l'EA sur le graphique XAUUSD H1</li>
            <li>L'EA détectera automatiquement votre licence</li>
        </ol>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ url('/client/license') }}" style="background: #FFD700; color: #000; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                Accéder à ma licence
            </a>
        </div>

        <p style="color: #666; font-size: 14px;">
            <strong>Support technique :</strong><br>
            Email : support@14trades.com<br>
            Discord : discord.gg/14trades<br>
            WhatsApp : +221 77 123 4567
        </p>
    </div>

    <div style="background: #0B1220; color: #8FA3C8; padding: 20px; text-align: center; font-size: 12px;">
        <p style="margin: 0;">© 2024 14TRADES. Tous droits réservés.</p>
        <p style="margin: 10px 0 0;">Cet email est envoyé automatiquement, merci de ne pas y répondre.</p>
    </div>
</div>
</body>
</html>
