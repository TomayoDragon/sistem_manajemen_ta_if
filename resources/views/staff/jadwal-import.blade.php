<x-staff-layout>
    <x-slot name="title">
        Import Jadwal Excel
    </x-slot>

    <style>
        .form-input {
            width: 100%; padding: 12px 15px; font-size: 1rem;
            border: 1px solid #ccc; border-radius: 8px; margin-top: 10px;
        }
        .btn-submit {
            padding: 12px 25px; font-size: 1rem; font-weight: 700; color: #fff;
            background-color: #0a2e6c; border: none; border-radius: 8px;
            cursor: pointer; margin-top: 20px;
        }
        .error-box-validation {
            background-color: #ffebeB; color: #a00; margin-bottom: 20px;
            padding: 20px; border-radius: 8px;
        }
        .error-box-validation ul {
            list-style-type: disc; padding-left: 20px;
        }
    </style>

    <h1 class="content-title">Import Jadwal dari Excel (Langkah 2)</h1>

    @if ($errors->any())
        <div class="error-box-validation">
            <strong>Error Validasi File:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('import_errors'))
        <div class="error-box-validation">
            <strong>Ditemukan Error Saat Import Data (Proses Dibatalkan):</strong>
            <ul>
                @foreach (session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    @if (session('error'))
        <div class="error-box-validation">
            <strong>Terjadi Kesalahan Fatal:</strong>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="content-box">
        <p style="color: #555; font-size: 1rem; margin-bottom: 20px;">
            Silakan upload file `DRAF_JADWAL_SIDANG.xlsx` yang sudah Anda periksa dan finalisasi.
        </p>

        <form action="{{ route('staff.jadwal.import.process') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="form-group">
                <label for="file_jadwal">Upload File Jadwal Final (.xlsx)</label>
                <input type="file" id="file_jadwal" name="file_jadwal" class="form-input" 
                       accept=".xlsx" required>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="fa-solid fa-upload"></i> Import dan Publish Jadwal
            </button>
        </form>
    </div>
</x-staff-layout>