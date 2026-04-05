@extends('layout')

@section('content')
<div class="main-content main-content-expanded" id="mainContent">

    <div class="mb-4">
        <h2 class="fw-bold mb-1">Paramètres de la société</h2>
        <p class="text-muted mb-0">
            Configurez les informations affichées dans les factures et documents.
        </p>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">

            <div class="row g-4">

                <!-- LEFT INFO -->
                <div class="col-lg-5">
                    <div class="card border-0 rounded-4 h-100" style="background:#f8fafc;">
                        <div class="card-body p-4">

                            <div class="mb-3"
                                 style="width:64px;height:64px;border-radius:18px;background:linear-gradient(135deg,#0d6efd,#4f46e5);color:#fff;display:flex;align-items:center;justify-content:center;font-size:26px;">
                                <i class="fas fa-building"></i>
                            </div>

                            <h4 class="fw-bold mb-2">Informations société</h4>
                            <p class="text-muted mb-4">
                                Ces informations seront utilisées automatiquement dans les factures PDF.
                            </p>

                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div style="width:38px;height:38px;border-radius:12px;background:#dbeafe;color:#2563eb;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-image"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">Logo</div>
                                    <small class="text-muted">Affiché en haut du PDF</small>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div style="width:38px;height:38px;border-radius:12px;background:#dcfce7;color:#16a34a;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">Contact</div>
                                    <small class="text-muted">Téléphone et email</small>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-3">
                                <div style="width:38px;height:38px;border-radius:12px;background:#f3e8ff;color:#7c3aed;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">Footer facture</div>
                                    <small class="text-muted">Texte en bas du document</small>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- RIGHT FORM -->
                <div class="col-lg-7">
                    <form action="{{ route('settings.company.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nom de la société</label>
                            <input type="text" name="company_name"
                                   value="{{ old('company_name', $setting->company_name ?? '') }}"
                                   class="form-control rounded-4"
                                   placeholder="Nom de votre société">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Ville</label>
                            <input type="text" name="city"
                                   value="{{ old('city', $setting->city ?? '') }}"
                                   class="form-control rounded-4">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Téléphone</label>
                            <input type="text" name="phone"
                                   value="{{ old('phone', $setting->phone ?? '') }}"
                                   class="form-control rounded-4">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email"
                                   value="{{ old('email', $setting->email ?? '') }}"
                                   class="form-control rounded-4">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Adresse</label>
                            <textarea name="address"
                                      class="form-control rounded-4"
                                      rows="2">{{ old('address', $setting->address ?? '') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Texte de garantie</label>
                            <textarea name="footer_note"
                                      class="form-control rounded-4"
                                      rows="3">{{ old('footer_note', $setting->footer_note ?? '') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Contact footer</label>
                            <textarea name="footer_contact"
                                      class="form-control rounded-4"
                                      rows="2">{{ old('footer_contact', $setting->footer_contact ?? '') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Logo</label>
                            <input type="file" name="logo" class="form-control rounded-4">
                        </div>

                        @if(!empty($setting->logo))
                            <div class="mb-3">
                                <img src="{{ asset('storage/' . $setting->logo) }}"
                                     style="max-height:100px"
                                     class="rounded shadow-sm">
                            </div>
                        @endif

                        <div class="d-flex justify-content-end gap-2 flex-wrap mt-4">
                            <a href="{{ url()->previous() }}" class="btn btn-light rounded-pill px-4">
                                Annuler
                            </a>

                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                <i class="fas fa-save me-2"></i>Enregistrer
                            </button>
                        </div>

                    </form>
                </div>

            </div>

        </div>
    </div>

</div>
@endsection