<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estado de cuenta - {{ $inscripcion->equipo->nombre }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #1f2937; }
        .header { text-align: center; margin-bottom: 15px; }
        .header h1 { font-size: 20px; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #f3f4f6; text-align: left; padding: 6px 8px; border-bottom: 2px solid #d1d5db; }
        td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
        .vencido { color: #b91c1c; font-weight: bold; }
        .saldo { font-size: 18px; font-weight: bold; text-align: right; margin-top: 15px; }
        .footer { margin-top: 30px; font-size: 10px; color: #6b7280; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LigaManager</h1>
        <p>Estado de cuenta</p>
    </div>

    <table>
        <tr>
            <td><strong>Equipo:</strong> {{ $inscripcion->equipo->nombre }}</td>
            <td><strong>Categoría:</strong> {{ $inscripcion->categoria->nombre }}</td>
            <td><strong>Torneo:</strong> {{ $inscripcion->categoria->torneo->nombre }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Concepto</th>
                <th>Monto</th>
                <th>Pagado</th>
                <th>Saldo</th>
                <th>Vencimiento</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($cargos as $cargo)
                <tr>
                    <td>{{ $cargo->conceptoCobro->nombre }}</td>
                    <td>${{ number_format($cargo->monto, 2) }}</td>
                    <td>${{ number_format($cargo->totalPagado(), 2) }}</td>
                    <td>${{ number_format($cargo->saldoPendiente(), 2) }}</td>
                    <td class="{{ $cargo->estaVencido() ? 'vencido' : '' }}">
                        {{ $cargo->fecha_vencimiento?->format('d/m/Y') ?? '—' }}
                        {{ $cargo->estaVencido() ? '(vencido)' : '' }}
                    </td>
                    <td>{{ ucfirst($cargo->estado->value) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Este equipo no tiene cargos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="saldo">Saldo total pendiente: ${{ number_format($saldoTotal, 2) }} MXN</div>

    <div class="footer">
        Generado por LigaManager el {{ now()->format('d/m/Y H:i') }}.
    </div>
</body>
</html>
