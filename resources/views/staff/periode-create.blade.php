<x-staff-layout>
    <x-slot name="title">Kelola Periode</x-slot>

    <style>
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: 700; margin-bottom: 5px; color: #333; font-size: 0.9rem;}
        .form-input, .form-select { 
            width: 100%; padding: 10px; font-size: 0.95rem; 
            border: 1px solid #ccc; border-radius: 6px; 
        }
        .btn-submit {
            width: 100%; padding: 10px; font-weight: 700; color: white; border: none; border-radius: 6px; cursor: pointer;
        }
        .btn-blue { background-color: #0a2e6c; }
        .btn-green { background-color: #16a085; }

        /* Layout 2 Kolom */
        .grid-container {
            display: grid; grid-template-columns: 1fr 1fr; gap: 25px;
        }
        @media (max-width: 900px) { .grid-container { grid-template-columns: 1fr; } }

        /* Badge & Table */
        .table-wrapper { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 0.9rem; }
        .table-wrapper th, .table-wrapper td { border: 1px solid #eee; padding: 8px; text-align: left; }
        .table-wrapper th { background-color: #f8f9fa; font-weight: 700; }
        
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; display: inline-block;}
        .badge-active { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .badge-inactive { background-color: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; }
        .badge-expired { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        .btn-icon { background: none; border: none; cursor: pointer; padding: 5px; font-size: 1rem; }
        .text-danger { color: #e74c3c; }
        .text-success { color: #2ecc71; }
        .text-muted { color: #ccc; }
    </style>

    {{-- Notifikasi --}}
    @if (session('success'))
        <div class="content-box" style="background-color: #eBffeb; color: #0a0; margin-bottom: 20px;">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="content-box" style="background-color: #ffebeB; color: #a00; margin-bottom: 20px;">{{ session('error') }}</div>
    @endif

    <div class="grid-container">
        
        {{-- SECTION 1: PERIODE AKADEMIK --}}
        <div>
            <h2 class="content-title" style="border-bottom: 2px solid #0a2e6c; padding-bottom: 10px;">1. Periode Akademik</h2>
            
            <div class="content-box" style="margin-bottom: 20px;">
                <form action="{{ route('staff.periode.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Nama Semester</label>
                        <input type="text" name="nama" class="form-input" placeholder="Cth: Semester Ganjil 2026/2027" required>
                    </div>
                    <div class="form-group" style="display:flex; gap:10px;">
                        <div style="flex:1">
                            <label>Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-input" required>
                        </div>
                        <div style="flex:1">
                            <label>Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-input" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="checkbox" name="is_active" id="active_akademik" value="1">
                        <label for="active_akademik" style="display:inline; font-weight:normal;">Set Aktif</label>
                    </div>
                    <button type="submit" class="btn-submit btn-blue"><i class="fa fa-save"></i> Simpan Akademik</button>
                </form>
            </div>

            {{-- TABEL AKADEMIK --}}
            <div class="content-box">
                <table class="table-wrapper">
                    <thead><tr><th>Periode</th><th>Rentang</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                        @foreach ($periodes as $p)
                        @php $isExpired = $p->tanggal_selesai < now()->format('Y-m-d'); @endphp
                        <tr>
                            <td>{{ $p->nama }}</td>
                            <td><small>{{ \Carbon\Carbon::parse($p->tanggal_mulai)->format('d/m/y') }} - {{ \Carbon\Carbon::parse($p->tanggal_selesai)->format('d/m/y') }}</small></td>
                            <td>
                                @if($p->is_active) <span class="badge badge-active">AKTIF</span>
                                @elseif($isExpired) <span class="badge badge-expired">LEWAT</span>
                                @else <span class="badge badge-inactive">NONAKTIF</span> @endif
                            </td>
                            <td>
                                {{-- Tombol Toggle Aktif --}}
                                @if(!$isExpired)
                                    <form action="{{ route('staff.periode.activate', ['type'=>'akademik', 'id'=>$p->id]) }}" method="POST" style="display:inline;">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn-icon {{ $p->is_active ? 'text-success' : 'text-muted' }}" title="Klik untuk ubah status">
                                            <i class="fa fa-power-off"></i>
                                        </button>
                                    </form>
                                    
                                    {{-- Tombol Hapus (Hanya jika belum mulai) --}}
                                    @if($p->tanggal_mulai > now())
                                        <form action="{{ route('staff.periode.destroy', ['type'=>'akademik', 'id'=>$p->id]) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus?');">
                                            @csrf @method('DELETE')
                                            <button class="btn-icon text-danger"><i class="fa fa-trash"></i></button>
                                        </form>
                                    @endif
                                @else
                                    <i class="fa fa-lock text-muted"></i>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- SECTION 2: PERIODE SIDANG --}}
        <div>
            <h2 class="content-title" style="border-bottom: 2px solid #16a085; padding-bottom: 10px;">2. Periode Sidang</h2>
            
            <div class="content-box" style="margin-bottom: 20px;">
                <form action="{{ route('staff.periode.storeSidang') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Induk Semester</label>
                        <select name="periode_id" class="form-select" required>
                            @foreach($periodes as $p)
                                @if($p->tanggal_selesai >= now()) {{-- Hanya tampilkan semester aktif/masa depan --}}
                                    <option value="{{ $p->id }}">{{ $p->nama }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nama Gelombang</label>
                        <input type="text" name="nama_event" class="form-input" placeholder="Cth: Gelombang 1 - Agustus" required>
                    </div>
                    <div class="form-group" style="display:flex; gap:10px;">
                        <div style="flex:1">
                            <label>Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-input" required>
                        </div>
                        <div style="flex:1">
                            <label>Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-input" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="checkbox" name="is_published" id="active_sidang" value="1">
                        <label for="active_sidang" style="display:inline; font-weight:normal;">Buka Pendaftaran (Aktif)</label>
                    </div>
                    <button type="submit" class="btn-submit btn-green"><i class="fa fa-save"></i> Simpan Sidang</button>
                </form>
            </div>

            {{-- TABEL SIDANG --}}
            <div class="content-box">
                <table class="table-wrapper">
                    <thead><tr><th>Gelombang</th><th>Induk</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                        @foreach ($events as $e)
                        @php $isExpiredEvent = $e->tanggal_selesai < now()->format('Y-m-d'); @endphp
                        <tr>
                            <td>
                                <strong>{{ $e->nama_event }}</strong><br>
                                <small>{{ \Carbon\Carbon::parse($e->tanggal_mulai)->format('d/m') }} - {{ \Carbon\Carbon::parse($e->tanggal_selesai)->format('d/m/y') }}</small>
                            </td>
                            <td>{{ $e->periode->nama ?? '-' }}</td>
                            <td>
                                @if($e->is_published) <span class="badge badge-active">BUKA</span>
                                @elseif($isExpiredEvent) <span class="badge badge-expired">TUTUP</span>
                                @else <span class="badge badge-inactive">TUTUP</span> @endif
                            </td>
                            <td>
                                {{-- Tombol Toggle --}}
                                @if(!$isExpiredEvent)
                                    <form action="{{ route('staff.periode.activate', ['type'=>'sidang', 'id'=>$e->id]) }}" method="POST" style="display:inline;">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn-icon {{ $e->is_published ? 'text-success' : 'text-muted' }}" title="Buka/Tutup Pendaftaran">
                                            <i class="fa fa-toggle-{{ $e->is_published ? 'on' : 'off' }}"></i>
                                        </button>
                                    </form>

                                    @if($e->tanggal_mulai > now())
                                        <form action="{{ route('staff.periode.destroy', ['type'=>'sidang', 'id'=>$e->id]) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus?');">
                                            @csrf @method('DELETE')
                                            <button class="btn-icon text-danger"><i class="fa fa-trash"></i></button>
                                        </form>
                                    @endif
                                @else
                                    <i class="fa fa-lock text-muted"></i>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-staff-layout>