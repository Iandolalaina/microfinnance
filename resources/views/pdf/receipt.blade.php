<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 13px; color: #1f2937; }
        .header { text-align: center; margin-bottom: 30px; }
        .brand { font-size: 22px; font-weight: bold; color: #2563eb; }
        .title { font-size: 16px; margin-top: 5px; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        td { padding: 8px 0; border-bottom: 1px solid #e5e7eb; }
        .label { color: #6b7280; width: 50%; }
        .value { font-weight: bold; text-align: right; }
        .amount-box {
            margin-top: 25px; padding: 15px; background: #eff6ff;
            border-radius: 8px; text-align: center;
        }
        .amount-box .amount { font-size: 24px; font-weight: bold; color: #2563eb; }
        .footer { margin-top: 40px; font-size: 11px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>

    <div class="header">
        <div class="brand">MITSINJO</div>
        <div class="title">Reçu de versement</div>
    </div>

    <table>
        <tr>
            <td class="label">Numéro de reçu</td>
            <td class="value">#{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</td>
        </tr>
        <tr>
            <td class="label">Date du versement</td>
            <td class="value">{{ $payment->paid_at?->translatedFormat('d F Y à H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Client</td>
            <td class="value">{{ $payment->client->name }}</td>
        </tr>
        <tr>
            <td class="label">Téléphone</td>
            <td class="value">{{ $payment->client->phone }}</td>
        </tr>
        <tr>
            <td class="label">Méthode de paiement</td>
            <td class="value">
                {{ $payment->method === 'mvola' ? 'Mvola' : ($payment->method === 'cash' ? 'Espèces' : 'Autre') }}
            </td>
        </tr>
        @if($payment->mvola_transaction_id)
        <tr>
            <td class="label">Référence transaction</td>
            <td class="value">{{ $payment->mvola_transaction_id }}</td>
        </tr>
        @endif
        <tr>
            <td class="label">Prêt concerné</td>
            <td class="value">Prêt #{{ $payment->loan_id }}</td>
        </tr>
    </table>

    <div class="amount-box">
        <div style="font-size: 12px; color: #6b7280;">Montant versé</div>
        <div class="amount">{{ number_format($payment->amount, 0, ',', ' ') }} Ar</div>
    </div>

    <div class="footer">
        Ce reçu a été généré automatiquement par la plateforme MITSINJO.<br>
        Merci de le conserver comme preuve de votre versement.
    </div>

</body>
</html>
