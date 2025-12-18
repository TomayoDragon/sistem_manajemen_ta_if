<x-staff-layout>
    <x-slot name="title">
        Kelola Periode Akademik
    </x-slot>

    <style>
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 700; margin-bottom: 8px; color: #333; }
        .form-input { 
            width: 100%; padding: 12px; font-size: 1rem; 
            border: 1px solid #ccc; border-radius: 8px; 
        }
        .btn-submit {
            padding: 12px 30px; font-size: 1rem; font-weight: 700; 
            color: white; background-color: #0a2e6c; border: none; 
            border-radius: 8px; cursor: pointer;
        }
        .checkbox-wrapper {
            display: flex; align-items: center; margin-top: 10px;
        }
        .checkbox-wrapper input { width: 20px; height: 20px; margin-right: 10px; }

        /* Style Tabel Bawah */
        .table-wrapper { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table-wrapper th, .table-wrapper td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .table-wrapper th { background-color: #f4f4f4; font-weight: 700; }
        
        .badge { padding: 5px 10px; border-radius: 5px; font-size: 0.85rem; font-weight: bold; }
        .badge-active { background-color: #2ecc71; color: white; }
        .badge-inactive { background-color: #95a5a6; color: white; }

        .btn-delete {
            padding: 5px 10px; background-color: #e74c3c; color: white; 
            border: none; border-radius: 5px; cursor: pointer; font-size: 0.9rem;
        }
        .btn-delete:hover { background-color: #c0392b; }
        
        .status-locked {
            color: #777; font-style: italic; font-size: 0.9rem;
        }
    </style>

    @if (session('success'))
        <div class="content-box" style="background-color: #eBffeb; color: #0a0; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="content-box" style="background-color: #ffebeB; color: #a00; margin-bottom: 20px;">
            {{ session('error') }}
        </div>
    @endif

    <h1 class="content-title">Tambah Periode Baru</h1>

    <div class="content-box">
        <form action="{{ route('staff.periode.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="nama">Nama Periode</label>
                <input type="text" name="nama" id="nama" class="form-input" 
                       placeholder="Contoh: Semester Ganjil 2026/2027" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="tanggal_mulai">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="tanggal_selesai">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-input" required>
                </div>
            </div>

            <div class="form-group">
                <label>Status Aktif</label>
                <div class="checkbox-wrapper">
                    <input type="checkbox" name="is_active" id="is_active" value="1">
                    <label for="is_active" style="margin:0; font-weight: normal;">
                        Set sebagai Periode Aktif
                    </label>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fa-solid fa-plus-circle"></i> Simpan Periode
            </button>
        </form>
    </div>

    <h2 class="content-title" style="margin-top: 40px;">Daftar Periode Terdaftar</h2>
    
    <div class="content-box">
        <table class="table-wrapper">
            <thead>
                <tr>
                    <th>Nama Periode</th>
                    <th>Rentang Tanggal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($periodes as $periode)
                    <tr>
                        <td>{{ $periode->nama }}</td>
                        <td>
                            {{ \Carbon\Carbon::parse($periode->tanggal_mulai)->format('d M Y') }} 
                            s/d 
                            {{ \Carbon\Carbon::parse($periode->tanggal_selesai)->format('d M Y') }}
                        </td>
                        <td>
                            @if($periode->is_active)
                                <span class="badge badge-active">Aktif</span>
                            @else
                                <span class="badge badge-inactive">Tidak Aktif</span>
                            @endif
                        </td>
                        <td>
                            @if ($periode->tanggal_mulai > now())
                                <form action="{{ route('staff.periode.destroy', $periode->id) }}" method="POST" 
                                      onsubmit="return confirm('Yakin ingin menghapus periode masa depan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete">
                                        <i class="fa-solid fa-trash"></i> Hapus
                                    </button>
                                </form>
                            @else
                                <span class="status-locked">
                                    <i class="fa-solid fa-lock"></i> Terkunci
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</x-staff-layout>