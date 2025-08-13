<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Rekap Presensi</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ccc;
            text-align: left;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 18px;
        }

        .foto {
            width: 40px;
            height: 40px;
            object-fit: cover;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Rekap Data Presensi</h1>
        <p>{{ $titleDate }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Foto</th>
                <th>Nama</th>
                <th>Lokasi</th>
                <th>Tanggal</th>
                <th>Check In</th>
                <th>Status</th>
                <th>Alasan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($presensi as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    {{-- <td>
                        @php
                        $fotoPath = $item->foto_lokasi ? str_replace('public/', '', $item->foto_lokasi) : null;
                        @endphp
                        @if ($fotoPath)
                        <img src="{{ public_path('storage/' . $fotoPath) }}" alt="Foto" class="foto">
                        @else
                        -
                        @endif
                    </td> --}}

                    <td>
                        @if ($item->foto_base64)
                        <img src="{{ $item->foto_base64 }}" alt="Foto" class="foto">
                        @else
                        -
                        @endif
                    </td>
                    <td>{{ $item->user->name }}</td>
                    <td>{{ $item->lokasi ?? '-' }}</td>
                    <td>{{ $item->created_at->format('d M Y') }}</td>
                    <td>{{ $item->clock_in ? $item->clock_in->format('H:i:s') : '-' }}</td>
                    <td>{{ $item->status }}</td>
                    <td>{{ $item->catatan ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;">Tidak ada data untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>