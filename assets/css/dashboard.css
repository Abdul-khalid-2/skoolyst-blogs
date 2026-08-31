/* ============================================================
   dashboard.css — dashboard-only styles
   blog.skoolyst.com
   ============================================================ */

.dash-layout {
  display: flex;
  min-height: 100vh;
  background: var(--bg);
}

/* ---- Sidebar ---- */
.dash-sidebar {
  width: 256px;
  background: #fff;
  border-right: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0;
  bottom: 0;
  left: 0;
  z-index: 1040;
  transition: transform .3s ease;
}

.dash-sidebar .sidebar-brand {
  padding: 1.25rem 1.5rem;
  display: flex;
  align-items: center;
  gap: .5rem;
  font-weight: 800;
  font-size: 1.15rem;
  color: var(--primary);
  border-bottom: 1px solid var(--border);
  letter-spacing: -.02em;
}
.dash-sidebar .sidebar-brand .brand-dot {
  width: 10px; height: 10px; border-radius: 50%; background: var(--secondary);
}
.dash-sidebar .sidebar-brand .brand-sub {
  font-size: .7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .06em;
  color: var(--text-muted);
  margin-left: auto;
}

.dash-sidebar .sidebar-nav {
  flex: 1;
  padding: .75rem;
  overflow-y: auto;
  list-style: none;
  margin: 0;
}

.dash-sidebar .sidebar-nav li { margin-bottom: .15rem; }

.dash-sidebar .sidebar-nav a {
  display: flex;
  align-items: center;
  gap: .75rem;
  padding: .65rem .85rem;
  border-radius: var(--radius-sm);
  color: var(--text-muted);
  font-weight: 500;
  font-size: .92rem;
  transition: background .2s, color .2s;
}
.dash-sidebar .sidebar-nav a:hover {
  background: var(--bg-alt);
  color: var(--text);
}
.dash-sidebar .sidebar-nav a.active {
  background: var(--primary);
  color: #fff;
}
.dash-sidebar .sidebar-nav a .nav-icon {
  font-size: 1.1rem;
  width: 20px;
  text-align: center;
  flex-shrink: 0;
}

.dash-sidebar .sidebar-section-label {
  font-size: .7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .08em;
  color: var(--text-muted);
  padding: 1rem .85rem .4rem;
}

.dash-sidebar .sidebar-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--border);
  font-size: .8rem;
  color: var(--text-muted);
}

/* Sidebar toggle (mobile) */
.dash-sidebar-toggle {
  display: none;
  background: none;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  padding: .4rem .6rem;
  font-size: 1.2rem;
  cursor: pointer;
  color: var(--text);
  align-items: center;
  justify-content: center;
}

.sidebar-backdrop {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(15,23,42,.4);
  z-index: 1035;
}
.sidebar-backdrop.show { display: block; }

@media (max-width: 992px) {
  .dash-sidebar {
    transform: translateX(-100%);
    box-shadow: var(--shadow-hover);
  }
  .dash-sidebar.open { transform: translateX(0); }
  .dash-sidebar-toggle { display: flex; }
}

/* ---- Main content ---- */
.dash-main {
  flex: 1;
  margin-left: 256px;
  display: flex;
  flex-direction: column;
  min-width: 0;
}
@media (max-width: 992px) {
  .dash-main { margin-left: 0; }
}

/* ---- Topbar ---- */
.dash-topbar {
  background: #fff;
  border-bottom: 1px solid var(--border);
  padding: 1rem 1.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  position: sticky;
  top: 0;
  z-index: 1020;
}

.dash-topbar .topbar-title h1 {
  font-size: 1.35rem;
  margin: 0;
}
.dash-topbar .topbar-title p {
  font-size: .85rem;
  color: var(--text-muted);
  margin: 0;
}

.dash-topbar .topbar-actions {
  display: flex;
  align-items: center;
  gap: .75rem;
}

.dash-topbar .topbar-user {
  display: flex;
  align-items: center;
  gap: .6rem;
  padding-left: .75rem;
  border-left: 1px solid var(--border);
}
.dash-topbar .topbar-user img {
  width: 36px; height: 36px; border-radius: 50%; object-fit: cover;
}
.dash-topbar .topbar-user .user-name { font-weight: 600; font-size: .9rem; }
.dash-topbar .topbar-user .user-role { font-size: .8rem; color: var(--text-muted); }

@media (max-width: 576px) {
  .dash-topbar .topbar-user .user-name,
  .dash-topbar .topbar-user .user-role { display: none; }
}

/* ---- Buttons ---- */
.btn-primary-dash {
  display: inline-flex;
  align-items: center;
  gap: .4rem;
  padding: .55rem 1.1rem;
  background: var(--primary);
  color: #fff;
  border: none;
  border-radius: var(--radius-sm);
  font-weight: 600;
  font-size: .9rem;
  font-family: inherit;
  cursor: pointer;
  text-decoration: none;
  transition: background .2s;
}
.btn-primary-dash:hover { background: var(--primary-hover); color: #fff; }

.btn-secondary-dash {
  display: inline-flex;
  align-items: center;
  gap: .4rem;
  padding: .55rem 1.1rem;
  background: #fff;
  color: var(--text);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  font-weight: 600;
  font-size: .9rem;
  font-family: inherit;
  cursor: pointer;
  text-decoration: none;
  transition: all .2s;
}
.btn-secondary-dash:hover { border-color: var(--secondary); color: var(--secondary); }

.btn-danger-dash {
  display: inline-flex;
  align-items: center;
  gap: .4rem;
  padding: .55rem 1.1rem;
  background: var(--error);
  color: #fff;
  border: none;
  border-radius: var(--radius-sm);
  font-weight: 600;
  font-size: .9rem;
  font-family: inherit;
  cursor: pointer;
  transition: opacity .2s;
}
.btn-danger-dash:hover { opacity: .85; }

/* ---- Content area ---- */
.dash-content {
  padding: 1.5rem;
  flex: 1;
}

/* ---- Stat cards ---- */
.stat-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1.25rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: #fff;
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 1.5rem;
  box-shadow: var(--shadow-sm);
  transition: box-shadow .2s, transform .2s;
}
.stat-card:hover {
  box-shadow: var(--shadow);
  transform: translateY(-2px);
}
.stat-card .stat-icon {
  width: 48px; height: 48px;
  display: flex; align-items: center; justify-content: center;
  border-radius: var(--radius-sm);
  font-size: 1.4rem;
  margin-bottom: 1rem;
}
.stat-card .stat-icon.blue { background: rgba(15,64,119,.1); color: var(--primary); }
.stat-card .stat-icon.green { background: var(--success-bg); color: var(--success); }
.stat-card .stat-icon.amber { background: var(--warning-bg); color: var(--warning); }
.stat-card .stat-icon.purple { background: rgba(124,58,237,.1); color: #7c3aed; }

.stat-card .stat-label {
  font-size: .8rem;
  color: var(--text-muted);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .04em;
  margin-bottom: .25rem;
}
.stat-card .stat-value {
  font-size: 2rem;
  font-weight: 800;
  color: var(--text);
  line-height: 1.1;
}
.stat-card .stat-trend {
  font-size: .8rem;
  margin-top: .5rem;
  font-weight: 600;
}
.stat-card .stat-trend.up { color: var(--success); }
.stat-card .stat-trend.down { color: var(--error); }

/* ---- Dashboard card ---- */
.dash-card {
  background: #fff;
  border: 1px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--shadow-sm);
  margin-bottom: 1.5rem;
  overflow: hidden;
}
.dash-card .card-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}
.dash-card .card-header h3 {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 700;
}
.dash-card .card-body { padding: 1.5rem; }

/* ---- Bar chart ---- */
.bar-chart {
  display: flex;
  align-items: flex-end;
  gap: .5rem;
  height: 200px;
  padding-top: 1rem;
}
.bar-chart .bar-col {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: .4rem;
  min-width: 0;
}
.bar-chart .bar {
  width: 100%;
  max-width: 40px;
  background: linear-gradient(180deg, var(--secondary) 0%, var(--primary) 100%);
  border-radius: 6px 6px 0 0;
  transition: height .5s ease;
  min-height: 4px;
  position: relative;
}
.bar-chart .bar:hover { filter: brightness(1.1); }
.bar-chart .bar-label {
  font-size: .75rem;
  color: var(--text-muted);
  font-weight: 600;
}
.bar-chart .bar-value {
  font-size: .7rem;
  color: var(--text-muted);
  position: absolute;
  top: -18px;
  left: 50%;
  transform: translateX(-50%);
  white-space: nowrap;
  opacity: 0;
  transition: opacity .2s;
}
.bar-chart .bar-col:hover .bar-value { opacity: 1; }

/* ---- Table ---- */
.dash-table-wrap {
  overflow-x: auto;
}
table.dash-table {
  width: 100%;
  border-collapse: collapse;
  font-size: .9rem;
}
table.dash-table th {
  text-align: left;
  padding: .75rem 1rem;
  font-weight: 700;
  font-size: .8rem;
  text-transform: uppercase;
  letter-spacing: .04em;
  color: var(--text-muted);
  border-bottom: 1px solid var(--border);
  white-space: nowrap;
}
table.dash-table td {
  padding: .85rem 1rem;
  border-bottom: 1px solid var(--border);
  color: var(--text);
  vertical-align: middle;
}
table.dash-table tbody tr {
  transition: background .15s;
}
table.dash-table tbody tr:hover {
  background: var(--bg-alt);
}
table.dash-table tbody tr:last-child td {
  border-bottom: none;
}
table.dash-table .table-title {
  font-weight: 600;
  color: var(--text);
}
table.dash-table .table-title a { color: inherit; }
table.dash-table .table-title a:hover { color: var(--primary); }
table.dash-table .table-thumb {
  width: 48px; height: 36px;
  border-radius: 6px;
  object-fit: cover;
  flex-shrink: 0;
}

/* ---- Status badges ---- */
.badge-status {
  display: inline-flex;
  align-items: center;
  gap: .35rem;
  padding: .25rem .6rem;
  border-radius: 100px;
  font-size: .75rem;
  font-weight: 600;
}
.badge-status .badge-dot {
  width: 6px; height: 6px; border-radius: 50%;
}
.badge-status.published {
  background: var(--success-bg);
  color: var(--success);
}
.badge-status.published .badge-dot { background: var(--success); }
.badge-status.draft {
  background: var(--warning-bg);
  color: var(--warning);
}
.badge-status.draft .badge-dot { background: var(--warning); }

/* ---- Action buttons in table ---- */
.table-actions {
  display: flex;
  gap: .35rem;
}
.table-actions .action-btn {
  width: 32px; height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid var(--border);
  background: #fff;
  border-radius: var(--radius-sm);
  cursor: pointer;
  font-size: .85rem;
  color: var(--text-muted);
  transition: all .2s;
  text-decoration: none;
}
.table-actions .action-btn:hover {
  border-color: var(--secondary);
  color: var(--secondary);
}
.table-actions .action-btn.danger:hover {
  border-color: var(--error);
  color: var(--error);
}

/* ---- Filter bar (dashboard) ---- */
.dash-filter-bar {
  display: flex;
  gap: .75rem;
  flex-wrap: wrap;
  align-items: center;
  margin-bottom: 1.5rem;
}
.dash-filter-bar .filter-search {
  position: relative;
  flex: 1;
  min-width: 200px;
  max-width: 320px;
}
.dash-filter-bar .filter-search input {
  width: 100%;
  padding: .55rem 1rem .55rem 2.3rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  font-family: inherit;
  font-size: .9rem;
  background: #fff;
}
.dash-filter-bar .filter-search input:focus {
  outline: none;
  border-color: var(--secondary);
  box-shadow: 0 0 0 3px rgba(67,97,238,.15);
}
.dash-filter-bar .filter-search .search-icon {
  position: absolute;
  left: .7rem; top: 50%; transform: translateY(-50%);
  color: var(--text-muted);
}
.dash-filter-bar select {
  padding: .55rem 2rem .55rem .85rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  font-family: inherit;
  font-size: .9rem;
  background: #fff;
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2364748b' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506.001z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right .6rem center;
}

/* ---- Post editor ---- */
.editor-grid {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 1.5rem;
  align-items: start;
}
@media (max-width: 992px) {
  .editor-grid { grid-template-columns: 1fr; }
}

.editor-form .form-group { margin-bottom: 1.25rem; }
.editor-form label {
  display: block;
  font-weight: 600;
  font-size: .88rem;
  margin-bottom: .4rem;
}
.editor-form label .req { color: var(--error); }
.editor-form input[type="text"],
.editor-form input[type="url"],
.editor-form select,
.editor-form textarea {
  width: 100%;
  padding: .65rem .85rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  font-family: inherit;
  font-size: .92rem;
  background: #fff;
  transition: border-color .2s, box-shadow .2s;
}
.editor-form input:focus,
.editor-form select:focus,
.editor-form textarea:focus {
  outline: none;
  border-color: var(--secondary);
  box-shadow: 0 0 0 3px rgba(67,97,238,.15);
}
.editor-form textarea.body-area {
  min-height: 320px;
  font-family: var(--font-mono);
  font-size: .88rem;
  line-height: 1.6;
  resize: vertical;
}
.editor-form .form-hint {
  font-size: .8rem;
  color: var(--text-muted);
  margin-top: .3rem;
}
.editor-form .slug-row {
  display: flex;
  gap: .5rem;
}
.editor-form .slug-row input { flex: 1; }
.editor-form .slug-row button {
  padding: 0 1rem;
  background: var(--bg-alt);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  font-family: inherit;
  font-size: .85rem;
  cursor: pointer;
  color: var(--text-muted);
  white-space: nowrap;
}
.editor-form .slug-row button:hover { border-color: var(--secondary); color: var(--secondary); }

.editor-sidebar .sidebar-card {
  background: #fff;
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 1.25rem;
  margin-bottom: 1.25rem;
  box-shadow: var(--shadow-sm);
}
.editor-sidebar .sidebar-card h4 {
  font-size: .85rem;
  text-transform: uppercase;
  letter-spacing: .04em;
  color: var(--text-muted);
  margin: 0 0 .75rem;
  font-weight: 700;
}

/* Cover image upload */
.cover-upload {
  border: 2px dashed var(--border);
  border-radius: var(--radius-sm);
  padding: 1.5rem;
  text-align: center;
  cursor: pointer;
  transition: border-color .2s, background .2s;
}
.cover-upload:hover {
  border-color: var(--secondary);
  background: var(--bg-alt);
}
.cover-upload .upload-icon { font-size: 2rem; color: var(--text-muted); margin-bottom: .5rem; }
.cover-upload .upload-text { font-size: .85rem; color: var(--text-muted); }
.cover-preview {
  margin-top: .75rem;
  border-radius: var(--radius-sm);
  overflow: hidden;
}
.cover-preview img {
  width: 100%;
  border-radius: var(--radius-sm);
}

/* Status toggle */
.status-toggle {
  display: flex;
  gap: .5rem;
}
.status-toggle label {
  flex: 1;
  display: flex;
  align-items: center;
  gap: .4rem;
  padding: .5rem .75rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  cursor: pointer;
  font-size: .85rem;
  font-weight: 500;
  margin: 0;
  transition: all .2s;
}
.status-toggle input { margin: 0; }
.status-toggle label:has(input:checked) {
  border-color: var(--primary);
  background: rgba(15,64,119,.05);
  color: var(--primary);
}

/* Editor actions */
.editor-actions {
  display: flex;
  gap: .75rem;
  padding-top 1rem;
  border-top: 1px solid var(--border);
  margin-top: 1.5rem;
}

/* ---- Categories page ---- */
.cat-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--border);
}
.cat-row:last-child { border-bottom: none; }
.cat-row .cat-color {
  width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0;
}
.cat-row .cat-info { flex: 1; }
.cat-row .cat-name { font-weight: 600; margin: 0; }
.cat-row .cat-desc { font-size: .85rem; color: var(--text-muted); margin: 0; }
.cat-row .cat-count {
  font-size: .85rem;
  color: var(--text-muted);
  font-weight: 600;
  background: var(--bg-alt);
  padding: .25rem .6rem;
  border-radius: 100px;
}

/* ---- Media library ---- */
.media-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 1rem;
}
.media-item {
  background: #fff;
  border: 1px solid var(--border);
  border-radius: var(--radius);
  overflow: hidden;
  box-shadow: var(--shadow-sm);
  transition: box-shadow .2s, transform .2s;
}
.media-item:hover {
  box-shadow: var(--shadow);
  transform: translateY(-2px);
}
.media-item .media-thumb {
  aspect-ratio: 4 / 3;
  overflow: hidden;
  background: var(--bg-alt);
}
.media-item .media-thumb img {
  width: 100%; height: 100%; object-fit: cover;
}
.media-item .media-info {
  padding: .75rem;
}
.media-item .media-name {
  font-size: .8rem;
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin: 0 0 .15rem;
}
.media-item .media-meta {
  font-size: .72rem;
  color: var(--text-muted);
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.media-item .media-delete {
  background: none;
  border: none;
  color: var(--error);
  cursor: pointer;
  font-size: .85rem;
  padding: 0;
}
.media-item .media-delete:hover { text-decoration: underline; }

.media-upload-area {
  border: 2px dashed var(--border);
  border-radius: var(--radius);
  padding: 3rem 2rem;
  text-align: center;
  cursor: pointer;
  transition: border-color .2s, background .2s;
  margin-bottom: 1.5rem;
}
.media-upload-area:hover {
  border-color: var(--secondary);
  background: var(--bg-alt);
}
.media-upload-area .upload-icon { font-size: 3rem; color: var(--text-muted); opacity: .5; }
.media-upload-area .upload-text { font-weight: 600; margin: .5rem 0 .25rem; }
.media-upload-area .upload-hint { font-size: .85rem; color: var(--text-muted); }

/* ---- Modal ---- */
.modal-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(15,23,42,.5);
  z-index: 1080;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}
.modal-overlay.show { display: flex; }
.modal-box {
  background: #fff;
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-hover);
  width: 100%;
  max-width: 480px;
  max-height: 90vh;
  overflow-y: auto;
  animation: modalIn .25s ease;
}
@keyframes modalIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
.modal-box .modal-header {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.modal-box .modal-header h3 { margin: 0; font-size: 1.15rem; }
.modal-box .modal-close {
  background: none;
  border: none;
  font-size: 1.4rem;
  cursor: pointer;
  color: var(--text-muted);
  padding: 0;
  line-height: 1;
}
.modal-box .modal-body { padding: 1.5rem; }
.modal-box .modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--border);
  display: flex;
  justify-content: flex-end;
  gap: .5rem;
}

@media (prefers-reduced-motion: reduce) {
  .modal-box { animation: none; }
}
