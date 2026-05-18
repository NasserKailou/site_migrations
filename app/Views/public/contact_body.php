<?php use App\Core\Session; use App\Core\View; ?>
<!-- HERO -->
<section class="page-hero page-hero--blue">
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-light">
                <li class="breadcrumb-item"><a href="<?= url() ?>">Accueil</a></li>
                <li class="breadcrumb-item active">Contact</li>
            </ol>
        </nav>
        <h1 class="page-hero__title">Contactez-nous</h1>
        <p class="page-hero__subtitle">Une question sur les données ? Un partenariat ? Écrivez-nous.</p>
    </div>
</section>

<section class="section-py bg-light">
    <div class="container">
        <div class="row g-5">

            <!-- FORMULAIRE -->
            <div class="col-lg-7">

                <?php if ($flash = Session::flash('contact_success')): ?>
                <div class="alert alert-success alert-dismissible" role="alert">
                    <strong>Message envoyé !</strong> Nous vous répondrons dans les 48 heures ouvrées.
                    <button type="button" class="btn-close" aria-label="Fermer"
                            onclick="this.parentElement.remove()">×</button>
                </div>
                <?php elseif ($flash = Session::flash('error')): ?>
                <div class="alert alert-danger" role="alert"><?= esc($flash) ?></div>
                <?php endif; ?>

                <?php $old = Session::flash('old') ?? []; ?>

                <div class="contact-form-card">
                    <h2 class="contact-form-title">Envoyer un message</h2>

                    <form method="POST" action="<?= url('contact') ?>" id="contactForm" novalidate>
                        <?= csrf_field() ?>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="nom" class="form-label required">Nom complet</label>
                                    <input type="text" id="nom" name="nom" class="form-control"
                                           required autocomplete="name"
                                           value="<?= esc($old['nom'] ?? '') ?>"
                                           placeholder="Votre nom">
                                    <div class="invalid-feedback">Le nom est requis.</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="email" class="form-label required">Adresse email</label>
                                    <input type="email" id="email" name="email" class="form-control"
                                           required autocomplete="email"
                                           value="<?= esc($old['email'] ?? '') ?>"
                                           placeholder="votre@email.com">
                                    <div class="invalid-feedback">Email invalide.</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <label for="organisation" class="form-label">Organisation (optionnel)</label>
                            <input type="text" id="organisation" name="organisation" class="form-control"
                                   value="<?= esc($old['organisation'] ?? '') ?>"
                                   placeholder="Votre institution ou organisation">
                        </div>

                        <div class="form-group mt-3">
                            <label for="sujet" class="form-label required">Sujet</label>
                            <select id="sujet" name="sujet" class="form-control" required>
                                <option value="">— Sélectionner un sujet —</option>
                                <option value="question_donnees"   <?= ($old['sujet'] ?? '') === 'question_donnees'   ? 'selected' : '' ?>>Question sur les données</option>
                                <option value="demande_partenariat" <?= ($old['sujet'] ?? '') === 'demande_partenariat' ? 'selected' : '' ?>>Demande de partenariat</option>
                                <option value="erreur_plateforme"   <?= ($old['sujet'] ?? '') === 'erreur_plateforme'   ? 'selected' : '' ?>>Signaler une erreur</option>
                                <option value="acces_api"           <?= ($old['sujet'] ?? '') === 'acces_api'           ? 'selected' : '' ?>>Accès à l'API</option>
                                <option value="presse_media"        <?= ($old['sujet'] ?? '') === 'presse_media'        ? 'selected' : '' ?>>Presse & Médias</option>
                                <option value="autre"               <?= ($old['sujet'] ?? '') === 'autre'               ? 'selected' : '' ?>>Autre</option>
                            </select>
                        </div>

                        <div class="form-group mt-3">
                            <label for="message" class="form-label required">Message</label>
                            <textarea id="message" name="message" class="form-control" rows="6" required
                                      placeholder="Décrivez votre demande en détail…"
                                      minlength="20"><?= esc($old['message'] ?? '') ?></textarea>
                            <div class="d-flex justify-content-between mt-1">
                                <div class="invalid-feedback">Le message doit contenir au moins 20 caractères.</div>
                                <small id="charCount" class="text-muted">0 / 2000</small>
                            </div>
                        </div>

                        <!-- Honeypot anti-spam -->
                        <div style="display:none;" aria-hidden="true">
                            <input type="text" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="mt-4 d-flex gap-3 align-items-center">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                Envoyer le message
                            </button>
                            <small class="text-muted">
                                En soumettant ce formulaire, vous acceptez notre
                                <a href="<?= url('a-propos') ?>" class="text-muted">politique de confidentialité</a>.
                            </small>
                        </div>
                    </form>
                </div>
            </div>

            <!-- INFORMATIONS DE CONTACT -->
            <div class="col-lg-5">
                <h2 class="section-subtitle mb-4">Nos coordonnées</h2>

                <div class="contact-info-list">
                    <div class="contact-info-item">
                        <div class="contact-info-icon contact-info-icon--orange">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <div>
                            <strong>Adresse</strong>
                            <p>Institut National de la Statistique<br>
                               BP 13416, Niamey — Niger</p>
                        </div>
                    </div>

                    <div class="contact-info-item">
                        <div class="contact-info-icon contact-info-icon--orange">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </div>
                        <div>
                            <strong>Email général</strong>
                            <p><a href="mailto:pndm@ins.niger.ne">pndm@ins.niger.ne</a></p>
                        </div>
                    </div>

                    <div class="contact-info-item">
                        <div class="contact-info-icon contact-info-icon--green">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 9.63a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 8.91a16 16 0 0 0 6.99 7l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        </div>
                        <div>
                            <strong>Téléphone</strong>
                            <p><a href="tel:+22720722339">+227 20 72 23 39</a></p>
                        </div>
                    </div>

                    <div class="contact-info-item">
                        <div class="contact-info-icon contact-info-icon--green">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <div>
                            <strong>Horaires</strong>
                            <p>Lundi – Vendredi<br>8h00 – 17h00 (GMT+1)</p>
                        </div>
                    </div>
                </div>

                <!-- Équipe PNDM — contacts directs -->
                <div class="mt-4 p-3 bg-white rounded" style="box-shadow:0 2px 12px rgba(0,0,0,.07);border-radius:12px;">
                    <h3 style="font-size:.95rem;font-weight:700;color:var(--pndm-orange-dark);margin-bottom:.75rem;display:flex;align-items:center;gap:.5rem;">
                        <i class="fa-solid fa-users fa-sm" aria-hidden="true"></i>
                        Équipe technique PNDM
                    </h3>
                    <table style="width:100%;border-collapse:collapse;font-size:.82rem;">
                        <thead>
                            <tr style="background:var(--pndm-orange-light);">
                                <th style="padding:.5rem .75rem;text-align:left;font-weight:700;color:var(--pndm-orange-dark);border-radius:4px 0 0 0;">Nom & Prénom</th>
                                <th style="padding:.5rem .75rem;text-align:left;font-weight:700;color:var(--pndm-orange-dark);border-radius:0 4px 0 0;">Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $equipe = [
                                ['Mr Lamou Ousseini Youssoufa',        'youssoufa@ins.ne'],
                                ['Mr Abdoulaye IDRISSA BOUKARY',       'ibabdoulaye@ins.ne'],
                                ['Mr Souleymane HARO',                 'sharo@ins.ne'],
                                ['Mr Abdoul-Nasser Kailou Assoumane',  'akailou@ins.ne'],
                                ['Mr Abdoul Karim Bachirou Seydou',    'abachirou@ins.ne'],
                            ];
                            foreach ($equipe as $i => [$nom, $email]):
                            ?>
                            <tr style="background:<?= $i % 2 === 0 ? '#fff' : '#fafafa' ?>;border-bottom:1px solid var(--gray-200);">
                                <td style="padding:.5rem .75rem;color:var(--gray-800);font-weight:500;"><?= esc($nom) ?></td>
                                <td style="padding:.5rem .75rem;">
                                    <a href="mailto:<?= esc($email) ?>"
                                       style="color:var(--pndm-orange-dark);text-decoration:none;font-weight:500;"
                                       aria-label="Envoyer un email à <?= esc($nom) ?>">
                                        <i class="fa-regular fa-envelope fa-xs" aria-hidden="true"></i>
                                        <?= esc($email) ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Partenaires logos -->
                <div class="mt-3 p-3 bg-white rounded" style="box-shadow:0 1px 8px rgba(0,0,0,.06);border-radius:10px;">
                    <p style="font-size:.75rem;color:var(--gray-500);margin-bottom:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;">
                        Plateforme développée avec le soutien de :
                    </p>
                    <div style="display:flex;gap:1.25rem;align-items:center;flex-wrap:wrap;">
                        <img src="<?= View::asset('assets/images/iom-logo.png') ?>"
                             alt="OIM / IOM" height="38" style="object-fit:contain;"
                             onerror="this.style.display='none'">
                        <img src="<?= View::asset('assets/images/maeci-logo.png') ?>"
                             alt="MAECI Italie" height="32" style="object-fit:contain;"
                             onerror="this.style.display='none'">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.contact-form-card {
    background:#fff; border-radius:12px; padding:2.5rem;
    box-shadow:0 2px 20px rgba(0,0,0,.07);
}
.contact-form-title {
    font-size:1.3rem; font-weight:700; margin-bottom:1.5rem;
    color:var(--pndm-orange-dark);
}
.contact-info-list { display:flex; flex-direction:column; gap:1.25rem; }
.contact-info-item {
    display:flex; gap:1rem; align-items:flex-start;
    background:#fff; border-radius:10px; padding:1.25rem;
    box-shadow:0 1px 8px rgba(0,0,0,.06);
}
.contact-info-icon {
    width:44px; height:44px; border-radius:10px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
}
.contact-info-icon--blue   { background:rgba(0,91,154,.1);   color:#005B9A; }
.contact-info-icon--orange { background:rgba(244,161,29,.1); color:#F4A11D; }
.contact-info-icon--green  { background:rgba(29,164,98,.1);  color:#1DA462; }
.contact-info-item strong { display:block; font-size:.9rem; margin-bottom:.2rem; }
.contact-info-item p { margin:0; font-size:.9rem; color:#4b5563; line-height:1.5; }
.contact-info-item a { color:var(--pndm-orange-dark); text-decoration:none; }
.contact-info-item a:hover { text-decoration:underline; }
</style>

<script>
(function () {
    // Compteur caractères
    const msg   = document.getElementById('message');
    const count = document.getElementById('charCount');
    msg?.addEventListener('input', () => {
        const len = msg.value.length;
        if (count) {
            count.textContent = `${len} / 2000`;
            count.style.color = len > 1800 ? '#ef4444' : '';
        }
        if (len > 2000) msg.value = msg.value.slice(0, 2000);
    });

    // Validation HTML5 personnalisée
    const form   = document.getElementById('contactForm');
    const submit = document.getElementById('submitBtn');
    form?.addEventListener('submit', function (e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            form.classList.add('was-validated');
            return;
        }
        submit.disabled = true;
        submit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Envoi…';
    });
})();
</script>
