<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo {{ $pago->folio_recibo }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 13px; color: #1f2937; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 20px; margin: 0; }
        .folio { text-align: right; font-size: 14px; font-weight: bold; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td, th { padding: 6px 8px; text-align: left; }
        .caja { border: 1px solid #d1d5db; border-radius: 6px; padding: 15px; margin-bottom: 15px; }
        .monto { font-size: 22px; font-weight: bold; text-align: center; margin: 20px 0; }
        .firma { margin-top: 60px; text-align: center; }
        .firma .linea { border-top: 1px solid #1f2937; width: 250px; margin: 0 auto; padding-top: 5px; }
        .footer { margin-top: 30px; font-size: 10px; color: #6b7280; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LigaManager</h1>
        <p>Recibo de pago</p>
    </div>

    <div class="folio">Folio: {{ $pago->folio_recibo }}</div>

    <div class="caja">
        <table>
            <tr>
                <td><strong>Equipo:</strong></td>
                <td>{{ $pago->cargo->inscripcion->equipo->nombre }}</td>
                <td><strong>Fecha:</strong></td>
                <td>{{ $pago->fecha->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td><strong>Categoría:</strong></td>
                <td>{{ $pago->cargo->inscripcion->categoria->nombre }}</td>
                <td><strong>Torneo:</strong></td>
                <td>{{ $pago->cargo->inscripcion->categoria->torneo->nombre }}</td>
            </tr>
            <tr>
                <td><strong>Concepto:</strong></td>
                <td colspan="3">{{ $pago->cargo->conceptoCobro->nombre }}{{ $pago->cargo->descripcion ? ' — '.$pago->cargo->descripcion : '' }}</td>
            </tr>
            <tr>
                <td><strong>Método de pago:</strong></td>
                <td>{{ ucfirst($pago->metodo->value) }}</td>
                <td><strong>Registrado por:</strong></td>
                <td>{{ $pago->registradoPor->name }}</td>
            </tr>
        </table>
    </div>

    <div class="monto">
        Monto pagado: ${{ number_format($pago->monto, 2) }} MXN
    </div>

    <div class="firma">
        <div class="linea">Firma / sello</div>
    </div>

    <div class="footer">
        Recibo generado por LigaManager el {{ now()->format('d/m/Y H:i') }}.
    </div>
</body>
</html>
