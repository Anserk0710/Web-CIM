@if(session('success'))
    <div
        role="status"
        class="mt-12 rounded-2xl border border-green-300 bg-green-50 p-6 text-green-950"
    >
        <h2 class="font-semibold">
            Lamaran Berhasil Diterima
        </h2>

        <p class="mt-2 text-sm leading-6">
            {{ session('success') }}
        </p>
    </div>
@endif

@if(!$acceptingApplications)
    <section class="mt-12 rounded-2xl border border-amber-300 bg-amber-50 p-6">
        <h2 class="font-semibold text-amber-950">
            Lamaran untuk posisi ini sudah ditutup.
        </h2>

        <p class="mt-2 text-sm leading-6 text-amber-900">
            Informasi lowongan tetap dapat dibaca, namun pengiriman formulir lamaran saat ini sudah tidak aktif.
        </p>
    </section>
@elseif(!$canApply)
    <section class="mt-12 rounded-2xl border border-amber-300 bg-amber-50 p-6">
        <h2 class="font-semibold text-amber-950">
            Formulir lamaran belum tersedia.
        </h2>

        <p class="mt-2 text-sm leading-6 text-amber-900">
            Informasi privasi atau ketentuan persetujuan lamaran belum dikonfigurasi.
            Silakan hubungi tim kami melalui halaman Hubungi Kami.
        </p>

        <a
            href="{{ route('contact') }}"
            class="mt-4 inline-flex font-semibold text-cim-blue hover:text-cim-navy"
        >
            Hubungi Kami
        </a>
    </section>
@else
    <section
        id="application-form"
        class="mt-12 rounded-2xl border p-6 sm:p-8"
    >
        <h2 class="text-2xl font-bold text-cim-navy">
            Kirimkan Lamaran Pekerjaan
        </h2>

        <p class="mt-3 text-sm leading-6 text-slate-600">
            Dokumen CV Anda tersimpan dengan aman pada penyimpanan privat dan hanya dapat diakses oleh staf rekrutmen yang berwenang.
        </p>

        @if($errors->any())
            <div
                role="alert"
                class="mt-5 rounded-xl border border-red-300 bg-red-50 p-4 text-sm text-red-900"
            >
                <p class="font-semibold">
                    Lamaran belum dapat dikirimkan.
                </p>

                <p class="mt-1">
                    Mohon periksa kembali kolom isian yang ditandai di bawah ini.
                </p>
            </div>
        @endif

        <form
            action="{{ route('careers.apply', $career->slug) }}"
            method="POST"
            enctype="multipart/form-data"
            class="mt-6 space-y-5"
        >
            @csrf

            <input
                type="hidden"
                name="consent_version_id"
                value="{{ $activeConsent->id }}"
            >

            <div>
                <label
                    for="full_name"
                    class="mb-2 block text-sm font-medium"
                >
                    Nama Lengkap
                </label>

                <input
                    id="full_name"
                    name="full_name"
                    value="{{ old('full_name') }}"
                    required
                    maxlength="150"
                    autocomplete="name"
                    class="w-full rounded-lg border border-input px-3 py-2.5"
                >

                @error('full_name')
                    <p class="mt-1 text-sm text-red-700">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label
                        for="email"
                        class="mb-2 block text-sm font-medium"
                    >
                        Alamat Email
                    </label>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        maxlength="254"
                        autocomplete="email"
                        class="w-full rounded-lg border border-input px-3 py-2.5"
                    >

                    @error('email')
                        <p class="mt-1 text-sm text-red-700">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="phone"
                        class="mb-2 block text-sm font-medium"
                    >
                        Nomor Telepon / WhatsApp
                    </label>

                    <input
                        id="phone"
                        name="phone"
                        value="{{ old('phone') }}"
                        maxlength="32"
                        autocomplete="tel"
                        class="w-full rounded-lg border border-input px-3 py-2.5"
                    >

                    @error('phone')
                        <p class="mt-1 text-sm text-red-700">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <div>
                <label
                    for="introduction"
                    class="mb-2 block text-sm font-medium"
                >
                    Perkenalan Singkat (Opsional)
                </label>

                <textarea
                    id="introduction"
                    name="introduction"
                    rows="5"
                    maxlength="5000"
                    placeholder="Ceritakan secara singkat mengenai latar belakang, keahlian, dan motivasi Anda..."
                    class="w-full rounded-lg border border-input px-3 py-2.5"
                >{{ old('introduction') }}</textarea>

                @error('introduction')
                    <p class="mt-1 text-sm text-red-700">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <label
                    for="portfolio_url"
                    class="mb-2 block text-sm font-medium"
                >
                    Tautan Portofolio (Opsional)
                </label>

                <input
                    id="portfolio_url"
                    type="url"
                    name="portfolio_url"
                    value="{{ old('portfolio_url') }}"
                    maxlength="2048"
                    placeholder="https://..."
                    class="w-full rounded-lg border border-input px-3 py-2.5"
                >

                @error('portfolio_url')
                    <p class="mt-1 text-sm text-red-700">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <label
                    for="linkedin_url"
                    class="mb-2 block text-sm font-medium"
                >
                    Tautan Profil LinkedIn (Opsional)
                </label>

                <input
                    id="linkedin_url"
                    type="url"
                    name="linkedin_url"
                    value="{{ old('linkedin_url') }}"
                    maxlength="2048"
                    placeholder="https://linkedin.com/in/..."
                    class="w-full rounded-lg border border-input px-3 py-2.5"
                >

                @error('linkedin_url')
                    <p class="mt-1 text-sm text-red-700">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <label
                    for="cv"
                    class="mb-2 block text-sm font-medium"
                >
                    Berkas CV / Resume (Format PDF, Maksimal 5 MB)
                </label>

                <input
                    id="cv"
                    type="file"
                    name="cv"
                    accept=".pdf,application/pdf"
                    required
                    class="block w-full rounded-lg border border-input px-3 py-2.5 text-sm"
                >

                @error('cv')
                    <p class="mt-1 text-sm text-red-700">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="rounded-xl bg-slate-50 p-4">
                <p class="whitespace-pre-line text-sm leading-6 text-slate-700">
                    {{ $activeConsent->text }}
                </p>

                <p class="mt-3 text-xs text-slate-500">
                    Versi Persetujuan:
                    {{ $activeConsent->version }}
                </p>

                <label class="mt-4 flex items-start gap-3 text-sm leading-6">
                    <input
                        type="checkbox"
                        name="consent"
                        value="1"
                        required
                        @checked(old('consent'))
                        class="mt-1 size-4 shrink-0"
                    >

                    <span>
                        Saya telah membaca, memahami, dan menyetujui pemrosesan data pribadi untuk tahapan seleksi rekrutmen sesuai ketentuan di atas.
                    </span>
                </label>

                @error('consent')
                    <p class="mt-1 text-sm text-red-700">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div
                class="hidden"
                aria-hidden="true"
            >
                <label for="website">
                    Jangan isi kolom ini
                </label>

                <input
                    id="website"
                    type="text"
                    name="website"
                    tabindex="-1"
                    autocomplete="off"
                >
            </div>

            @error('career')
                <p
                    role="alert"
                    class="rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-800"
                >
                    {{ $message }}
                </p>
            @enderror

            <button
                type="submit"
                class="inline-flex rounded-lg bg-cim-blue px-5 py-3 font-semibold text-white hover:bg-cim-navy"
            >
                Kirim Lamaran Pekerjaan <span class="sr-only">Submit Application</span>
            </button>
        </form>
    </section>
@endif
