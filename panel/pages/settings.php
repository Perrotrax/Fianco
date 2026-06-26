<!-- ── Configuración ──────────────────────────────────────── -->
<div class="settings-grid">

  <div class="settings-nav">
    <button class="settings-nav-item active" data-section="profile" onclick="switchSection(this)">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
        <circle cx="12" cy="7" r="4"/>
      </svg>
      <span>Perfil</span>
    </button>
    <button class="settings-nav-item" data-section="security" onclick="switchSection(this)">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
      </svg>
      <span>Seguridad</span>
    </button>
    <button class="settings-nav-item" data-section="biometrics" onclick="switchSection(this)">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
      </svg>
      <span>Biometría</span>
    </button>
  </div>

  <div class="settings-content">

    <div class="settings-section active" id="section-profile">
      <p class="settings-section-title">Tu perfil</p>
      <p class="settings-section-sub">Datos de tu cuenta en el sistema</p>
      <div class="form-group">
        <label class="form-label">Nombre</label>
        <input class="form-input" type="text" id="profile-name" readonly>
      </div>
      <div class="form-group">
        <label class="form-label">Correo electrónico</label>
        <input class="form-input" type="email" id="profile-email" readonly>
      </div>
      <div class="form-group">
        <label class="form-label">Fecha de registro</label>
        <input class="form-input" type="text" id="profile-joined" readonly>
      </div>
      <a href="../dashboard.php" class="btn-save-settings" style="text-decoration:none;display:inline-flex;">
        Ir a mi dashboard de gastos
      </a>
    </div>

    <div class="settings-section" id="section-security">
      <p class="settings-section-title">Seguridad</p>
      <p class="settings-section-sub">Información sobre el acceso a tu cuenta</p>
      <div class="toggle-row">
        <div>
          <p class="toggle-label">Estado de biometría en base de datos</p>
          <p class="settings-section-sub" id="bio-db-status" style="margin-top:4px">—</p>
        </div>
        <span class="badge" id="bio-db-badge">—</span>
      </div>
      <div class="toggle-row">
        <div>
          <p class="toggle-label">Token local en este navegador</p>
          <p class="settings-section-sub" id="bio-local-status" style="margin-top:4px">—</p>
        </div>
        <span class="badge" id="bio-local-badge">—</span>
      </div>
      <p class="settings-section-sub" style="margin-top:16px">
        La autenticación biométrica usa un token seguro almacenado en la base de datos
        (<code>token_biometrico</code>) y en el navegador local. Puedes activarla o desactivarla
        desde la sección Biometría.
      </p>
    </div>

    <div class="settings-section" id="section-biometrics">
      <p class="settings-section-title">Autenticación biométrica</p>
      <p class="settings-section-sub">
        Habilita el ingreso con huella dactilar simulada en este dispositivo, igual que en el dashboard principal.
      </p>

      <div class="toggle-row">
        <div>
          <p class="toggle-label">Habilitar ingreso con huella</p>
          <p class="settings-section-sub" style="margin-top:4px">
            Guarda un token local y actualiza los campos <strong>biometrico</strong> y
            <strong>token_biometrico</strong> en la tabla usuarios.
          </p>
        </div>
        <label class="toggle-switch">
          <input type="checkbox" id="biometricToggle" onchange="toggleBiometria(this)">
          <span class="toggle-track"></span>
        </label>
      </div>

      <div id="bio-feedback" class="settings-section-sub" style="margin-top:12px;display:none"></div>
    </div>

  </div>
</div>
