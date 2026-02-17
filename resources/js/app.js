import './bootstrap';

// ── jQuery (debe ir primero) ──────────────────────────
import $ from 'jquery';
window.$ = window.jQuery = $;

// ── CSS de librerías (en orden) ───────────────────────
import '@fortawesome/fontawesome-free/css/all.min.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'admin-lte/dist/css/adminlte.min.css';
import 'toastr/build/toastr.min.css';
import 'select2/dist/css/select2.min.css';
import 'select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css';

// ── CSS propio ────────────────────────────────────────
import '../css/app.css';

// ── Bootstrap JS ─────────────────────────────────────
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

// ── AdminLTE ──────────────────────────────────────────
import 'admin-lte/dist/js/adminlte.min.js';

// ── Toastr ────────────────────────────────────────────
import * as toastr from 'toastr';
window.toastr = toastr.default ?? toastr;

// ── Select2 ───────────────────────────────────────────
import 'select2/dist/js/select2.full.min.js';