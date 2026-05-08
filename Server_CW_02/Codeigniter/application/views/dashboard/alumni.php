<?php
// Codeigniter/application/views/dashboard/alumni.php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Alumni - Alumni Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --primary:#0f3460; --secondary:#e94560; --sidebar-w:260px; --bg:#f0f2f5; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; background:var(--bg); display:flex; min-height:100vh; }

        /* Sidebar */
        .sidebar { width:var(--sidebar-w); background:linear-gradient(180deg,#0f3460 0%,#16213e 100%); display:flex; flex-direction:column; position:fixed; top:0; left:0; height:100vh; z-index:100; }
        .sidebar-brand { display:flex; align-items:center; padding:25px 20px; border-bottom:1px solid rgba(255,255,255,0.1); gap:12px; }
        .brand-icon { width:42px; height:42px; background:#e94560; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .brand-icon i { color:white; font-size:18px; }
        .brand-title { color:white; font-size:18px; font-weight:700; display:block; line-height:1.2; }
        .brand-subtitle { color:rgba(255,255,255,0.6); font-size:12px; display:block; }
        .sidebar-nav { list-style:none; padding:15px 0; flex:1; }
        .nav-link { display:flex; align-items:center; gap:12px; padding:13px 20px; color:rgba(255,255,255,0.7); text-decoration:none; transition:all 0.2s; border-left:3px solid transparent; font-size:14px; }
        .nav-link:hover { color:white; background:rgba(255,255,255,0.08); border-left-color:rgba(255,255,255,0.3); }
        .nav-link.active { color:white; background:rgba(233,69,96,0.2); border-left-color:#e94560; }
        .nav-link i { width:20px; font-size:16px; }
        .sidebar-footer { padding:15px 20px; border-top:1px solid rgba(255,255,255,0.1); display:flex; align-items:center; gap:10px; }
        .user-avatar { width:36px; height:36px; background:#e94560; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:700; flex-shrink:0; }
        .user-details { flex:1; min-width:0; }
        .user-name { color:white; font-size:13px; font-weight:600; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .user-role { color:rgba(255,255,255,0.5); font-size:11px; }
        .logout-btn { color:rgba(255,255,255,0.6); text-decoration:none; padding:6px; border-radius:6px; transition:all 0.2s; }
        .logout-btn:hover { color:#e94560; }

        /* Main */
        .main-content { margin-left:var(--sidebar-w); flex:1; display:flex; flex-direction:column; }
        .topbar { background:white; padding:0 30px; height:65px; display:flex; align-items:center; justify-content:space-between; box-shadow:0 2px 10px rgba(0,0,0,0.08); position:sticky; top:0; z-index:99; }
        .topbar-title { font-size:20px; font-weight:700; color:var(--primary); }
        .page-body { padding:30px; flex:1; }

        /* Filter Bar */
        .filter-bar { background:white; border-radius:15px; padding:20px 25px; box-shadow:0 4px 15px rgba(0,0,0,0.06); margin-bottom:25px; }
        .filter-bar label { font-weight:600; font-size:13px; color:var(--primary); }
        .form-select, .form-control { font-size:13px; border-radius:8px; }
        .form-select:focus, .form-control:focus { border-color:var(--primary); box-shadow:0 0 0 0.2rem rgba(15,52,96,0.15); }
        .btn-apply { background:linear-gradient(135deg,var(--primary),var(--secondary)); border:none; color:white; border-radius:8px; padding:8px 20px; font-size:13px; font-weight:600; }
        .btn-reset { background:white; border:2px solid #dee2e6; color:#6c757d; border-radius:8px; padding:8px 20px; font-size:13px; font-weight:600; }

        /* Alumni Cards */
        .alumni-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
            position: relative;
        }

        .alumni-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        }

        .alumni-avatar {
            width: 55px; height: 55px;
            border-radius: 50%;
            background: linear-gradient(135deg,var(--primary),var(--secondary));
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 20px; font-weight: 700;
            flex-shrink: 0;
        }

        .alumni-name { font-size: 15px; font-weight: 700; color: var(--primary); margin:0; }
        .alumni-title { font-size: 12px; color: #6c757d; margin: 2px 0 0; }

        .badge-programme {
            background: rgba(15,52,96,0.1);
            color: var(--primary);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-year {
            background: rgba(233,69,96,0.1);
            color: var(--secondary);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-sector {
            background: rgba(25,135,84,0.1);
            color: #198754;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .skill-tag {
            background: #f0f2f5;
            color: #495057;
            padding: 3px 8px;
            border-radius: 5px;
            font-size: 11px;
            margin: 2px;
            display: inline-block;
        }

        .featured-badge {
            position: absolute;
            top: 15px; right: 15px;
            background: #ffc107;
            color: #856404;
            padding: 3px 8px;
            border-radius: 5px;
            font-size: 10px;
            font-weight: 700;
        }

        /* Pagination */
        .pagination-bar { display:flex; align-items:center; justify-content:center; gap:10px; margin-top:25px; }
        .page-btn { padding:8px 16px; border:2px solid #dee2e6; background:white; border-radius:8px; font-size:13px; cursor:pointer; transition:all 0.2s; }
        .page-btn:hover, .page-btn.active { background:var(--primary); color:white; border-color:var(--primary); }
        .page-btn:disabled { opacity:0.5; cursor:not-allowed; }

        /* Results info */
        .results-info { color:#6c757d; font-size:13px; }

        /* Export btn */
        .btn-export { background:white; border:2px solid var(--primary); color:var(--primary); border-radius:8px; padding:8px 18px; font-size:13px; font-weight:600; cursor:pointer; transition:all 0.2s; }
        .btn-export:hover { background:var(--primary); color:white; }

        /* Loading */
        .loading-spinner { text-align:center; padding:40px; color:#aaa; }

        /* No results */
        .no-results { text-align:center; padding:60px 20px; color:#aaa; }
        .no-results i { font-size:50px; margin-bottom:15px; }
    </style>
</head>
<body>

<!-- Sidebar -->
<?php $this->load->view('dashboard/sidebar'); ?>

<div class="main-content">

    <!-- Topbar -->
    <div class="topbar">
        <div class="topbar-title">
            <i class="fas fa-user-graduate me-2 text-danger"></i>View Alumni
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="results-info" id="resultsInfo">Loading...</span>
            <button class="btn-export" onclick="exportAlumniCSV()">
                <i class="fas fa-file-csv me-1"></i>Export CSV
            </button>
        </div>
    </div>

    <div class="page-body">

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="row g-3 align-items-end">

                <!-- Search -->
                <div class="col-md-3">
                    <label class="form-label mb-1">
                        <i class="fas fa-search me-1"></i>Search
                    </label>
                    <input type="text"
                           class="form-control"
                           id="searchInput"
                           placeholder="Name, job title, employer..."
                           onkeyup="debounceSearch()">
                </div>

                <!-- Programme -->
                <div class="col-md-2">
                    <label class="form-label mb-1">Programme</label>
                    <select class="form-select" id="filterProgramme">
                        <option value="all">All Programmes</option>
                        <?php foreach ($filter_options['programmes'] as $prog): ?>
                            <option value="<?php echo htmlspecialchars($prog); ?>">
                                <?php echo htmlspecialchars($prog); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Graduation Year -->
                <div class="col-md-2">
                    <label class="form-label mb-1">Graduation Year</label>
                    <select class="form-select" id="filterYear">
                        <option value="all">All Years</option>
                        <?php foreach ($filter_options['graduation_years'] as $year): ?>
                            <option value="<?php echo $year; ?>">
                                <?php echo $year; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Industry Sector -->
                <div class="col-md-2">
                    <label class="form-label mb-1">Industry</label>
                    <select class="form-select" id="filterSector">
                        <option value="all">All Sectors</option>
                        <?php foreach ($filter_options['industry_sectors'] as $sector): ?>
                            <option value="<?php echo htmlspecialchars($sector); ?>">
                                <?php echo htmlspecialchars($sector); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button class="btn-apply flex-grow-1 py-2" onclick="applyFilters()">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                        <button class="btn-reset py-2 px-3" onclick="resetFilters()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alumni Grid -->
        <div class="row g-4" id="alumniGrid">
            <div class="col-12 loading-spinner">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2">Loading alumni...</p>
            </div>
        </div>

        <!-- Pagination -->
        <div class="pagination-bar" id="paginationBar"></div>

    </div>
</div>

<!-- Alumni Detail Modal -->
<div class="modal fade" id="alumniModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius:15px;">
            <div class="modal-header border-0"
                 style="background:linear-gradient(135deg,#0f3460,#e94560); border-radius:15px 15px 0 0;">
                <h5 class="modal-title text-white">Alumni Profile</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="modalContent">
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const API_URL = '<?php echo $api_url; ?>';
const API_KEY = '<?php echo $api_key; ?>';

const HEADERS = {
    'Authorization': 'Bearer ' + API_KEY,
    'Content-Type': 'application/json'
};

let currentPage = 1;
let totalPages  = 1;
let searchTimer = null;
let allAlumniData = []; // store for CSV export

// ── Debounce search input ─────────────────────────────────────────────────
function debounceSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        currentPage = 1;
        loadAlumni();
    }, 500);
}

// ── Apply filters ─────────────────────────────────────────────────────────
function applyFilters() {
    currentPage = 1;
    loadAlumni();
}

// ── Reset all filters ─────────────────────────────────────────────────────
function resetFilters() {
    document.getElementById('searchInput').value      = '';
    document.getElementById('filterProgramme').value  = 'all';
    document.getElementById('filterYear').value       = 'all';
    document.getElementById('filterSector').value     = 'all';
    currentPage = 1;
    loadAlumni();
}

// ── Load alumni from API ──────────────────────────────────────────────────
async function loadAlumni() {
    const grid = document.getElementById('alumniGrid');
    grid.innerHTML = `
        <div class="col-12 loading-spinner">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p class="mt-2">Loading alumni...</p>
        </div>
    `;

    const params = new URLSearchParams({
        page:            currentPage,
        limit:           12,
        search:          document.getElementById('searchInput').value,
        programme:       document.getElementById('filterProgramme').value,
        graduation_year: document.getElementById('filterYear').value,
        industry_sector: document.getElementById('filterSector').value
    });

    try {
        const res  = await fetch(`${API_URL}/alumni?${params}`, { headers: HEADERS });
        const json = await res.json();

        if (!json.success) {
            grid.innerHTML = '<div class="col-12 no-results"><i class="fas fa-exclamation-circle"></i><p>Failed to load alumni</p></div>';
            return;
        }

        allAlumniData = json.data;
        totalPages    = json.pagination.total_pages;

        document.getElementById('resultsInfo').textContent =
            `Showing ${json.data.length} of ${json.pagination.total} alumni`;

        if (json.data.length === 0) {
            grid.innerHTML = `
                <div class="col-12 no-results">
                    <i class="fas fa-user-slash"></i>
                    <p>No alumni found matching your filters</p>
                    <button class="btn-reset mt-2 py-2 px-4" onclick="resetFilters()">
                        Clear Filters
                    </button>
                </div>
            `;
            document.getElementById('paginationBar').innerHTML = '';
            return;
        }

        // Render cards
        grid.innerHTML = json.data.map(a => renderAlumniCard(a)).join('');

        // Render pagination
        renderPagination(json.pagination);

    } catch (error) {
        grid.innerHTML = '<div class="col-12 no-results"><i class="fas fa-wifi"></i><p>API connection error. Make sure Node.js server is running.</p></div>';
    }
}

// ── Render single alumni card ─────────────────────────────────────────────
function renderAlumniCard(alumni) {
    const initials = alumni.full_name.split(' ').map(n => n[0]).join('').toUpperCase();
    const skills   = (alumni.skills || []).slice(0, 3);
    const moreSkills = (alumni.skills || []).length - 3;

    return `
        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="alumni-card" onclick="showAlumniDetail(${alumni.id})" 
                 style="cursor:pointer;">
                
                ${alumni.is_featured ? '<span class="featured-badge">⭐ Featured</span>' : ''}

                <!-- Header -->
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="alumni-avatar">${initials}</div>
                    <div>
                        <p class="alumni-name">${escapeHtml(alumni.full_name)}</p>
                        <p class="alumni-title">${escapeHtml(alumni.job_title || 'N/A')}</p>
                    </div>
                </div>

                <!-- Badges -->
                <div class="d-flex flex-wrap gap-1 mb-3">
                    <span class="badge-programme">${escapeHtml(alumni.programme)}</span>
                    <span class="badge-year">${alumni.graduation_year}</span>
                    <span class="badge-sector">${escapeHtml(alumni.industry_sector || 'N/A')}</span>
                </div>

                <!-- Employer & Location -->
                <div style="font-size:13px; color:#555; margin-bottom:12px;">
                    <div class="mb-1">
                        <i class="fas fa-building me-1 text-muted"></i>
                        ${escapeHtml(alumni.employer || 'N/A')}
                    </div>
                    <div>
                        <i class="fas fa-map-marker-alt me-1 text-muted"></i>
                        ${escapeHtml(alumni.location_city || '')}${alumni.location_city ? ', ' : ''}${escapeHtml(alumni.location_country || 'N/A')}
                    </div>
                </div>

                <!-- Skills -->
                <div>
                    ${skills.map(s => `<span class="skill-tag">${escapeHtml(s)}</span>`).join('')}
                    ${moreSkills > 0 ? `<span class="skill-tag">+${moreSkills} more</span>` : ''}
                </div>

            </div>
        </div>
    `;
}

// ── Show alumni detail in modal ───────────────────────────────────────────
async function showAlumniDetail(id) {
    const modal = new bootstrap.Modal(document.getElementById('alumniModal'));
    document.getElementById('modalContent').innerHTML = `
        <div class="text-center py-3">
            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
        </div>
    `;
    modal.show();

    try {
        const res    = await fetch(`${API_URL}/alumni/${id}`, { headers: HEADERS });
        const json   = await res.json();

        if (!json.success) {
            document.getElementById('modalContent').innerHTML =
                '<p class="text-danger">Failed to load profile.</p>';
            return;
        }

        const a = json.data;
        const initials = a.full_name.split(' ').map(n => n[0]).join('').toUpperCase();

        document.getElementById('modalContent').innerHTML = `
            <div class="d-flex align-items-center gap-3 mb-4">
                <div style="width:65px;height:65px;background:linear-gradient(135deg,#0f3460,#e94560);
                            border-radius:50%;display:flex;align-items:center;justify-content:center;
                            color:white;font-size:22px;font-weight:700;flex-shrink:0;">
                    ${initials}
                </div>
                <div>
                    <h5 style="margin:0;color:#0f3460;font-weight:700;">${escapeHtml(a.full_name)}</h5>
                    <p style="margin:3px 0;color:#6c757d;font-size:14px;">${escapeHtml(a.job_title || '')}</p>
                    <span class="badge-programme">${escapeHtml(a.programme)}</span>
                    <span class="badge-year ms-1">${a.graduation_year}</span>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-6">
                    <div style="background:#f8f9fa;padding:12px;border-radius:8px;">
                        <small style="color:#6c757d;font-weight:600;">EMPLOYER</small>
                        <p style="margin:4px 0 0;font-weight:600;">${escapeHtml(a.employer || 'N/A')}</p>
                    </div>
                </div>
                <div class="col-6">
                    <div style="background:#f8f9fa;padding:12px;border-radius:8px;">
                        <small style="color:#6c757d;font-weight:600;">SECTOR</small>
                        <p style="margin:4px 0 0;font-weight:600;">${escapeHtml(a.industry_sector || 'N/A')}</p>
                    </div>
                </div>
                <div class="col-6">
                    <div style="background:#f8f9fa;padding:12px;border-radius:8px;">
                        <small style="color:#6c757d;font-weight:600;">LOCATION</small>
                        <p style="margin:4px 0 0;font-weight:600;">
                            ${escapeHtml(a.location_city || '')}, ${escapeHtml(a.location_country || '')}
                        </p>
                    </div>
                </div>
                <div class="col-6">
                    <div style="background:#f8f9fa;padding:12px;border-radius:8px;">
                        <small style="color:#6c757d;font-weight:600;">GRADUATED</small>
                        <p style="margin:4px 0 0;font-weight:600;">${a.graduation_year}</p>
                    </div>
                </div>
            </div>

            ${a.skills && a.skills.length > 0 ? `
                <div class="mb-3">
                    <small style="color:#6c757d;font-weight:600;display:block;margin-bottom:8px;">SKILLS</small>
                    ${a.skills.map(s => `<span class="skill-tag">${escapeHtml(s)}</span>`).join('')}
                </div>
            ` : ''}

            ${a.certifications && a.certifications.length > 0 ? `
                <div>
                    <small style="color:#6c757d;font-weight:600;display:block;margin-bottom:8px;">CERTIFICATIONS</small>
                    ${a.certifications.map(c =>
                        `<span style="background:rgba(25,135,84,0.1);color:#198754;padding:3px 10px;border-radius:20px;font-size:11px;margin:2px;display:inline-block;">${escapeHtml(c)}</span>`
                    ).join('')}
                </div>
            ` : ''}
        `;

    } catch (e) {
        document.getElementById('modalContent').innerHTML =
            '<p class="text-danger">Failed to load profile.</p>';
    }
}

// ── Render pagination ─────────────────────────────────────────────────────
function renderPagination(pagination) {
    const bar = document.getElementById('paginationBar');

    if (pagination.total_pages <= 1) {
        bar.innerHTML = '';
        return;
    }

    let html = '';

    // Prev
    html += `<button class="page-btn" ${currentPage === 1 ? 'disabled' : ''}
                onclick="goToPage(${currentPage - 1})">
                <i class="fas fa-chevron-left"></i>
             </button>`;

    // Page numbers
    for (let i = 1; i <= pagination.total_pages; i++) {
        if (i === currentPage) {
            html += `<button class="page-btn active">${i}</button>`;
        } else if (i === 1 || i === pagination.total_pages ||
                   (i >= currentPage - 1 && i <= currentPage + 1)) {
            html += `<button class="page-btn" onclick="goToPage(${i})">${i}</button>`;
        } else if (i === currentPage - 2 || i === currentPage + 2) {
            html += `<span style="padding:0 5px;color:#aaa;">...</span>`;
        }
    }

    // Next
    html += `<button class="page-btn" ${currentPage === pagination.total_pages ? 'disabled' : ''}
                onclick="goToPage(${currentPage + 1})">
                <i class="fas fa-chevron-right"></i>
             </button>`;

    bar.innerHTML = html;
}

function goToPage(page) {
    currentPage = page;
    loadAlumni();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ── Export CSV ────────────────────────────────────────────────────────────
function exportAlumniCSV() {
    if (!allAlumniData.length) return;

    let csv = 'Name,Programme,Graduation Year,Industry,Job Title,Employer,City,Country\n';
    allAlumniData.forEach(a => {
        csv += `"${a.full_name}","${a.programme}",${a.graduation_year},"${a.industry_sector}","${a.job_title}","${a.employer}","${a.location_city}","${a.location_country}"\n`;
    });

    const blob = new Blob([csv], { type: 'text/csv' });
    const url  = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href  = url;
    link.download = 'alumni_data.csv';
    link.click();
}

// ── XSS protection helper ─────────────────────────────────────────────────
function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// Load alumni on page load
loadAlumni();
</script>
</body>
</html>