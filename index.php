<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
$admin_username = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin');
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg: #0e0c0a;
            --surface: #161412;
            --surface2: #1e1b18;
            --border: #2a2520;
            --gold: #c9a84c;
            --gold-light: #e8c96e;
            --cream: #f5ede0;
            --muted: #7a6f63;
            --text: #e8ddd0;
            --danger: #c0392b;
            --success: #27ae60;
            --warning: #e67e22;
            --radius: 8px;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
        }

        /* Sidebar */
        .sidebar {
            width: 240px;
            min-height: 100vh;
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
        }

        .logo {
            padding: 28px 24px 20px;
            border-bottom: 1px solid var(--border);
        }

        .logo h1 {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            color: var(--gold);
            letter-spacing: 0.5px;
        }

        .logo span {
            display: block;
            font-size: 11px;
            color: var(--muted);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 2px;
        }

        nav { padding: 16px 12px; flex: 1; }

        .nav-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--muted);
            padding: 8px 12px 6px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: var(--radius);
            cursor: pointer;
            color: var(--muted);
            font-size: 14px;
            font-weight: 500;
            transition: all 0.15s;
            margin-bottom: 2px;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }

        .nav-item:hover { background: var(--surface2); color: var(--text); }
        .nav-item.active { background: rgba(201, 168, 76, 0.12); color: var(--gold); }

        .nav-item svg { width: 18px; height: 18px; flex-shrink: 0; }

        .sidebar-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border);
            font-size: 12px;
            color: var(--muted);
        }

        /* Main Content */
        .main {
            margin-left: 240px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .topbar {
            padding: 20px 32px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--surface);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            color: var(--cream);
        }

        .topbar-actions { display: flex; gap: 10px; align-items: center; }

        .search-box {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 8px 14px;
        }

        .search-box input {
            background: none;
            border: none;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            outline: none;
            width: 200px;
        }

        .search-box input::placeholder { color: var(--muted); }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: var(--radius);
            border: none;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.15s;
        }

        .btn-primary { background: var(--gold); color: #0e0c0a; }
        .btn-primary:hover { background: var(--gold-light); }
        .btn-ghost { background: var(--surface2); color: var(--text); border: 1px solid var(--border); }
        .btn-ghost:hover { border-color: var(--gold); color: var(--gold); }
        .btn-danger { background: rgba(192, 57, 43, 0.15); color: var(--danger); border: 1px solid rgba(192,57,43,0.3); }
        .btn-danger:hover { background: rgba(192, 57, 43, 0.25); }
        .btn-success { background: rgba(39,174,96,0.15); color: var(--success); border: 1px solid rgba(39,174,96,0.3); }
        .btn-success:hover { background: rgba(39,174,96,0.25); }
        .btn-sm { padding: 5px 12px; font-size: 12px; }

        .content { padding: 32px; }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px 24px;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
        }

        .stat-card.books::before { background: var(--gold); }
        .stat-card.members::before { background: #6c8ebf; }
        .stat-card.borrowed::before { background: var(--warning); }
        .stat-card.overdue::before { background: var(--danger); }

        .stat-label { font-size: 12px; color: var(--muted); text-transform: uppercase; letter-spacing: 1.5px; }
        .stat-value { font-size: 36px; font-family: 'Playfair Display', serif; color: var(--cream); margin: 6px 0 0; }

        .stat-icon {
            position: absolute;
            right: 20px; top: 20px;
            width: 36px; height: 36px;
            opacity: 0.15;
        }

        /* Table */
        .table-container {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }

        .table-header {
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border);
        }

        .table-title { font-weight: 600; color: var(--cream); }

        .filter-tabs { display: flex; gap: 4px; }
        .filter-tab {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            cursor: pointer;
            border: 1px solid transparent;
            color: var(--muted);
            background: none;
            font-family: 'DM Sans', sans-serif;
            transition: all 0.15s;
        }
        .filter-tab.active { border-color: var(--gold); color: var(--gold); }
        .filter-tab:hover:not(.active) { color: var(--text); }

        table { width: 100%; border-collapse: collapse; }

        th {
            text-align: left;
            padding: 12px 20px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--muted);
            background: rgba(255,255,255,0.02);
            border-bottom: 1px solid var(--border);
            font-weight: 500;
        }

        td {
            padding: 14px 20px;
            font-size: 14px;
            border-bottom: 1px solid rgba(42,37,32,0.6);
            vertical-align: middle;
        }

        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(255,255,255,0.02); }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }

        .badge-success { background: rgba(39,174,96,0.15); color: var(--success); }
        .badge-warning { background: rgba(230,126,34,0.15); color: var(--warning); }
        .badge-danger { background: rgba(192,57,43,0.15); color: var(--danger); }
        .badge-muted { background: rgba(122,111,99,0.15); color: var(--muted); }
        .badge-blue { background: rgba(108,142,191,0.15); color: #6c8ebf; }

        .avail-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }

        .bar-track {
            width: 60px;
            height: 4px;
            background: var(--border);
            border-radius: 2px;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            border-radius: 2px;
            background: var(--success);
            transition: width 0.3s;
        }

        .actions { display: flex; gap: 6px; }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--muted);
        }

        .empty-state svg { width: 48px; height: 48px; margin-bottom: 12px; opacity: 0.3; }
        .empty-state p { font-size: 15px; }

        /* Modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s;
        }

        .modal-overlay.active { opacity: 1; pointer-events: all; }

        .modal {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            width: 480px;
            max-width: 95vw;
            max-height: 85vh;
            overflow-y: auto;
            transform: translateY(16px);
            transition: transform 0.2s;
        }

        .modal-overlay.active .modal { transform: translateY(0); }

        .modal-head {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-head h3 {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            color: var(--cream);
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            font-size: 20px;
            line-height: 1;
            padding: 2px;
        }

        .modal-close:hover { color: var(--text); }

        .modal-body { padding: 24px; }

        .form-group { margin-bottom: 16px; }

        .form-group label {
            display: block;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted);
            margin-bottom: 6px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            padding: 10px 14px;
            outline: none;
            transition: border-color 0.15s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus { border-color: var(--gold); }

        .form-group select option { background: var(--surface2); }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        /* Toast */
        .toast-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 2000;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .toast {
            background: var(--surface);
            border: 1px solid var(--border);
            border-left: 3px solid var(--gold);
            border-radius: var(--radius);
            padding: 12px 16px;
            font-size: 13px;
            min-width: 260px;
            animation: slideIn 0.3s ease;
        }

        .toast.error { border-left-color: var(--danger); }
        .toast.success { border-left-color: var(--success); }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .page-section { display: none; }
        .page-section.active { display: block; }

        /* Loading */
        .loading {
            text-align: center;
            padding: 40px;
            color: var(--muted);
            font-size: 14px;
        }

        @media (max-width: 900px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="logo">
        <h1>Library</h1>
        <span>Management System</span>
    </div>
    <nav>
        <div class="nav-label">Menu</div>
        <button class="nav-item active" onclick="navigate('dashboard')">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Dashboard
        </button>
        <button class="nav-item" onclick="navigate('books')">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
            Books
        </button>
        <button class="nav-item" onclick="navigate('members')">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
            Members
        </button>
        <button class="nav-item" onclick="navigate('borrowings')">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
            Borrowings
        </button>
    </nav>
    <div class="sidebar-footer">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
            <div style="width:32px;height:32px;border-radius:50%;background:rgba(201,168,76,0.15);border:1px solid rgba(201,168,76,0.3);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="16" height="16" fill="none" stroke="#c9a84c" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <div style="overflow:hidden;">
                <div style="font-size:13px;font-weight:500;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= $admin_username ?></div>
                <div style="font-size:11px;color:var(--muted);">Administrator</div>
            </div>
        </div>
        <a href="logout.php" style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:6px;color:var(--muted);font-size:13px;text-decoration:none;transition:all 0.15s;border:1px solid transparent;" onmouseover="this.style.background='rgba(192,57,43,0.1)';this.style.color='#e74c3c';this.style.borderColor='rgba(192,57,43,0.2)'" onmouseout="this.style.background='';this.style.color='var(--muted)';this.style.borderColor='transparent'">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Sign Out
        </a>
        
    </div>
</aside>

<!-- Main -->
<div class="main">
    <div class="topbar">
        <h2 class="page-title" id="pageTitle">Dashboard</h2>
        <div class="topbar-actions">
            <div class="search-box" id="searchBox" style="display:none">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                <input type="text" id="searchInput" placeholder="Search..." oninput="handleSearch(this.value)">
            </div>
            <button class="btn btn-primary" id="addBtn" style="display:none" onclick="openAddModal()">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span id="addBtnLabel">Add</span>
            </button>
        </div>
    </div>

    <div class="content">

        <!-- Dashboard -->
        <div class="page-section active" id="page-dashboard">
            <div class="stats-grid">
                <div class="stat-card books">
                    <div class="stat-label">Total Books</div>
                    <div class="stat-value" id="stat-books">—</div>
                    <svg class="stat-icon" fill="currentColor" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20V2H6.5A2.5 2.5 0 004 4.5v15z"/></svg>
                </div>
                <div class="stat-card members">
                    <div class="stat-label">Active Members</div>
                    <div class="stat-value" id="stat-members">—</div>
                    <svg class="stat-icon" fill="currentColor" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 7a4 4 0 100 8 4 4 0 000-8z"/></svg>
                </div>
                <div class="stat-card borrowed">
                    <div class="stat-label">Books Borrowed</div>
                    <div class="stat-value" id="stat-borrowed">—</div>
                    <svg class="stat-icon" fill="currentColor" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                </div>
                <div class="stat-card overdue">
                    <div class="stat-label">Overdue</div>
                    <div class="stat-value" id="stat-overdue">—</div>
                    <svg class="stat-icon" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">Recent Borrowings</div>
                    <button class="btn btn-ghost btn-sm" onclick="navigate('borrowings')">View All</button>
                </div>
                <div id="recentBorrowings"><div class="loading">Loading...</div></div>
            </div>

            <div style="margin-top:24px" class="table-container">
                <div class="table-header">
                    <div class="table-title">Activity Log <span style="font-size:11px;color:var(--muted);font-weight:400;margin-left:8px">All actions recorded to database</span></div>
                    <button class="btn btn-ghost btn-sm" onclick="loadActivityLog()">↻ Refresh</button>
                </div>
                <div id="activityLog"><div class="loading">Loading...</div></div>
            </div>
        </div>

        <!-- Books -->
        <div class="page-section" id="page-books">
            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">Book Catalog</div>
                </div>
                <div id="booksTable"><div class="loading">Loading...</div></div>
            </div>
        </div>

        <!-- Members -->
        <div class="page-section" id="page-members">
            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">Members</div>
                </div>
                <div id="membersTable"><div class="loading">Loading...</div></div>
            </div>
        </div>

        <!-- Borrowings -->
        <div class="page-section" id="page-borrowings">
            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">Borrowings</div>
                    <div class="filter-tabs">
                        <button class="filter-tab active" onclick="filterBorrowings('all',this)">All</button>
                        <button class="filter-tab" onclick="filterBorrowings('borrowed',this)">Active</button>
                        <button class="filter-tab" onclick="filterBorrowings('overdue',this)">Overdue</button>
                        <button class="filter-tab" onclick="filterBorrowings('returned',this)">Returned</button>
                    </div>
                </div>
                <div id="borrowingsTable"><div class="loading">Loading...</div></div>
            </div>
        </div>

    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modal" onclick="if(event.target===this)closeModal()">
    <div class="modal">
        <div class="modal-head">
            <h3 id="modalTitle">Add Book</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalBody"></div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
            <button class="btn btn-primary" onclick="submitModal()" id="modalSubmit">Save</button>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="toast-container" id="toastContainer"></div>

<script>
const API = './';
let currentPage = 'dashboard';
let modalMode = null;
let modalType = null;
let editId = null;
let allBooks = [];
let allMembers = [];
let currentBorrowFilter = 'all';
let searchTimer = null;

// Navigation
function navigate(page) {
    document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.page-section').forEach(el => el.classList.remove('active'));
    
    const navBtns = document.querySelectorAll('.nav-item');
    const pageMap = ['dashboard','books','members','borrowings'];
    navBtns[pageMap.indexOf(page)]?.classList.add('active');
    
    document.getElementById('page-' + page).classList.add('active');
    document.getElementById('pageTitle').textContent = { dashboard:'Dashboard', books:'Books', members:'Members', borrowings:'Borrowings' }[page];
    
    const searchBox = document.getElementById('searchBox');
    const addBtn = document.getElementById('addBtn');
    
    currentPage = page;
    
    if (page === 'dashboard') {
        searchBox.style.display = 'none';
        addBtn.style.display = 'none';
        loadDashboard();
    } else {
        searchBox.style.display = 'flex';
        document.getElementById('searchInput').value = '';
        addBtn.style.display = page !== 'borrowings' || true ? 'flex' : 'none';
        
        if (page === 'books') { document.getElementById('addBtnLabel').textContent = 'Add Book'; loadBooks(); }
        else if (page === 'members') { document.getElementById('addBtnLabel').textContent = 'Add Member'; loadMembers(); }
        else if (page === 'borrowings') { document.getElementById('addBtnLabel').textContent = 'Issue Book'; loadBorrowings(); }
    }
}

// API Helpers
async function api(endpoint, method = 'GET', body = null) {
    const opts = { method, headers: { 'Content-Type': 'application/json' } };
    if (body) opts.body = JSON.stringify(body);
    
    let url = API + endpoint;
    const res = await fetch(url, opts);
    return res.json();
}

// Toast
function toast(msg, type = 'success') {
    const el = document.createElement('div');
    el.className = `toast ${type}`;
    el.textContent = msg;
    document.getElementById('toastContainer').appendChild(el);
    setTimeout(() => el.remove(), 3500);
}

// Dashboard
async function loadDashboard() {
    const stats = await api('borrowings.php?stats=1');
    document.getElementById('stat-books').textContent = stats.totalBooks;
    document.getElementById('stat-members').textContent = stats.totalMembers;
    document.getElementById('stat-borrowed').textContent = stats.borrowed;
    document.getElementById('stat-overdue').textContent = stats.overdue;
    
    const borrowings = await api('borrowings.php');
    const recent = borrowings.slice(0, 8);
    renderRecentBorrowings(recent);
    loadActivityLog();
}

async function loadActivityLog() {
    document.getElementById('activityLog').innerHTML = '<div class="loading">Loading...</div>';
    const logs = await api('borrowings.php?activity=1&limit=15');
    if (!logs.length) {
        document.getElementById('activityLog').innerHTML = '<div class="empty-state"><p>No activity recorded yet</p></div>';
        return;
    }
    const iconMap = {
        add_book: '📚', edit_book: '✏️', delete_book: '🗑️',
        add_member: '👤', edit_member: '✏️', delete_member: '🗑️',
        issue_book: '📤', return_book: '📥'
    };
    const colorMap = {
        add_book: 'badge-success', edit_book: 'badge-blue', delete_book: 'badge-danger',
        add_member: 'badge-success', edit_member: 'badge-blue', delete_member: 'badge-danger',
        issue_book: 'badge-warning', return_book: 'badge-success'
    };
    document.getElementById('activityLog').innerHTML = `
        <table>
            <thead><tr>
                <th>Action</th><th>Description</th><th>Time</th>
            </tr></thead>
            <tbody>${logs.map(l => `
                <tr>
                    <td><span class="badge ${colorMap[l.action_type] || 'badge-muted'}">${iconMap[l.action_type] || ''} ${l.action_type.replace(/_/g,' ')}</span></td>
                    <td style="color:var(--text)">${esc(l.description)}</td>
                    <td style="color:var(--muted);font-size:12px">${new Date(l.performed_at).toLocaleString()}</td>
                </tr>`).join('')}
            </tbody>
        </table>`;
}

function renderRecentBorrowings(data) {
    if (!data.length) {
        document.getElementById('recentBorrowings').innerHTML = `<div class="empty-state"><p>No borrowings yet</p></div>`;
        return;
    }
    document.getElementById('recentBorrowings').innerHTML = `
        <table>
            <thead><tr>
                <th>Book</th><th>Member</th><th>Borrow Date</th><th>Due Date</th><th>Status</th>
            </tr></thead>
            <tbody>${data.map(b => `
                <tr>
                    <td><strong>${esc(b.book_title)}</strong><br><small style="color:var(--muted)">${esc(b.author)}</small></td>
                    <td>${esc(b.member_name)}</td>
                    <td>${b.borrow_date}</td>
                    <td>${b.due_date}</td>
                    <td>${statusBadge(b.status)}</td>
                </tr>`).join('')}
            </tbody>
        </table>`;
}

// Books
async function loadBooks(search = '') {
    const url = search ? `books.php?search=${encodeURIComponent(search)}` : 'books.php';
    allBooks = await api(url);
    renderBooks(allBooks);
}

function renderBooks(books) {
    if (!books.length) {
        document.getElementById('booksTable').innerHTML = `<div class="empty-state"><svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg><p>No books found</p></div>`;
        return;
    }
    document.getElementById('booksTable').innerHTML = `
        <table>
            <thead><tr>
                <th>Title / Author</th><th>ISBN</th><th>Genre</th><th>Year</th><th>Availability</th><th>Actions</th>
            </tr></thead>
            <tbody>${books.map(b => `
                <tr>
                    <td><strong>${esc(b.title)}</strong><br><small style="color:var(--muted)">${esc(b.author)}</small></td>
                    <td style="color:var(--muted);font-size:12px">${esc(b.isbn || '—')}</td>
                    <td>${b.genre ? `<span class="badge badge-blue">${esc(b.genre)}</span>` : '—'}</td>
                    <td style="color:var(--muted)">${b.published_year || '—'}</td>
                    <td>
                        <div class="avail-bar">
                            <div class="bar-track"><div class="bar-fill" style="width:${b.total_copies > 0 ? (b.available_copies/b.total_copies*100) : 0}%;background:${b.available_copies === 0 ? 'var(--danger)' : 'var(--success)'}"></div></div>
                            <span style="font-size:12px;color:var(--muted)">${b.available_copies}/${b.total_copies}</span>
                        </div>
                    </td>
                    <td><div class="actions">
                        <button class="btn btn-ghost btn-sm" onclick="editBook(${b.id})">Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="deleteBook(${b.id},'${esc(b.title)}')">Delete</button>
                    </div></td>
                </tr>`).join('')}
            </tbody>
        </table>`;
}

// Members
async function loadMembers(search = '') {
    const url = search ? `members.php?search=${encodeURIComponent(search)}` : 'members.php';
    allMembers = await api(url);
    renderMembers(allMembers);
}

function renderMembers(members) {
    if (!members.length) {
        document.getElementById('membersTable').innerHTML = `<div class="empty-state"><p>No members found</p></div>`;
        return;
    }
    document.getElementById('membersTable').innerHTML = `
        <table>
            <thead><tr>
                <th>Name</th><th>Email</th><th>Phone</th><th>Member Since</th><th>Status</th><th>Actions</th>
            </tr></thead>
            <tbody>${members.map(m => `
                <tr>
                    <td><strong>${esc(m.name)}</strong></td>
                    <td style="color:var(--muted)">${esc(m.email)}</td>
                    <td style="color:var(--muted)">${esc(m.phone || '—')}</td>
                    <td style="color:var(--muted)">${m.membership_date}</td>
                    <td><span class="badge ${m.status === 'active' ? 'badge-success' : 'badge-muted'}">${m.status}</span></td>
                    <td><div class="actions">
                        <button class="btn btn-ghost btn-sm" onclick="editMember(${m.id})">Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="deleteMember(${m.id},'${esc(m.name)}')">Delete</button>
                    </div></td>
                </tr>`).join('')}
            </tbody>
        </table>`;
}

// Borrowings
async function loadBorrowings() {
    const url = currentBorrowFilter === 'all' ? 'borrowings.php' : `borrowings.php?status=${currentBorrowFilter}`;
    const data = await api(url);
    renderBorrowings(data);
}

function filterBorrowings(status, el) {
    document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    currentBorrowFilter = status;
    loadBorrowings();
}

function renderBorrowings(data) {
    if (!data.length) {
        document.getElementById('borrowingsTable').innerHTML = `<div class="empty-state"><p>No borrowings found</p></div>`;
        return;
    }
    document.getElementById('borrowingsTable').innerHTML = `
        <table>
            <thead><tr>
                <th>Book</th><th>Member</th><th>Borrowed</th><th>Due</th><th>Returned</th><th>Status</th><th>Actions</th>
            </tr></thead>
            <tbody>${data.map(b => `
                <tr>
                    <td><strong>${esc(b.book_title)}</strong><br><small style="color:var(--muted)">${esc(b.author)}</small></td>
                    <td>${esc(b.member_name)}<br><small style="color:var(--muted)">${esc(b.email)}</small></td>
                    <td>${b.borrow_date}</td>
                    <td>${b.due_date}</td>
                    <td>${b.return_date || '—'}</td>
                    <td>${statusBadge(b.status)}</td>
                    <td>${b.status !== 'returned' ? `<button class="btn btn-success btn-sm" onclick="returnBook(${b.id})">Return</button>` : '—'}</td>
                </tr>`).join('')}
            </tbody>
        </table>`;
}

// Search handler
function handleSearch(val) {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        if (currentPage === 'books') loadBooks(val);
        else if (currentPage === 'members') loadMembers(val);
    }, 300);
}

// Modal
function openAddModal() {
    modalMode = 'add';
    editId = null;
    if (currentPage === 'books') {
        modalType = 'book';
        document.getElementById('modalTitle').textContent = 'Add Book';
        document.getElementById('modalBody').innerHTML = bookForm();
    } else if (currentPage === 'members') {
        modalType = 'member';
        document.getElementById('modalTitle').textContent = 'Add Member';
        document.getElementById('modalBody').innerHTML = memberForm();
    } else if (currentPage === 'borrowings') {
        modalType = 'borrowing';
        document.getElementById('modalTitle').textContent = 'Issue Book';
        document.getElementById('modalBody').innerHTML = borrowingForm();
        populateBorrowingSelects();
    }
    document.getElementById('modal').classList.add('active');
}

async function editBook(id) {
    const book = await api(`books.php?id=${id}`);
    modalMode = 'edit'; editId = id; modalType = 'book';
    document.getElementById('modalTitle').textContent = 'Edit Book';
    document.getElementById('modalBody').innerHTML = bookForm(book);
    document.getElementById('modal').classList.add('active');
}

async function editMember(id) {
    const m = await api(`members.php?id=${id}`);
    modalMode = 'edit'; editId = id; modalType = 'member';
    document.getElementById('modalTitle').textContent = 'Edit Member';
    document.getElementById('modalBody').innerHTML = memberForm(m);
    document.getElementById('modal').classList.add('active');
}

function closeModal() {
    document.getElementById('modal').classList.remove('active');
}

async function submitModal() {
    if (modalType === 'book') {
        const data = {
            title: document.getElementById('f_title').value.trim(),
            author: document.getElementById('f_author').value.trim(),
            isbn: document.getElementById('f_isbn').value.trim(),
            genre: document.getElementById('f_genre').value.trim(),
            total_copies: document.getElementById('f_copies').value,
            published_year: document.getElementById('f_year').value,
        };
        if (!data.title || !data.author) return toast('Title and author required', 'error');
        
        if (modalMode === 'add') {
            const r = await api('books.php', 'POST', data);
            if (r.success) { toast('Book added!'); closeModal(); loadBooks(); }
            else toast(r.error || 'Error', 'error');
        } else {
            data.id = editId;
            const r = await api('books.php', 'PUT', data);
            if (r.success) { toast('Book updated!'); closeModal(); loadBooks(); }
            else toast(r.error || 'Error', 'error');
        }
    } else if (modalType === 'member') {
        const data = {
            name: document.getElementById('f_name').value.trim(),
            email: document.getElementById('f_email').value.trim(),
            phone: document.getElementById('f_phone').value.trim(),
            address: document.getElementById('f_address').value.trim(),
            status: document.getElementById('f_status')?.value || 'active',
        };
        if (!data.name || !data.email) return toast('Name and email required', 'error');
        
        if (modalMode === 'add') {
            const r = await api('members.php', 'POST', data);
            if (r.success) { toast('Member added!'); closeModal(); loadMembers(); }
            else toast(r.error || 'Error', 'error');
        } else {
            data.id = editId;
            const r = await api('members.php', 'PUT', data);
            if (r.success) { toast('Member updated!'); closeModal(); loadMembers(); }
            else toast(r.error || 'Error', 'error');
        }
    } else if (modalType === 'borrowing') {
        const data = {
            book_id: document.getElementById('f_book').value,
            member_id: document.getElementById('f_member').value,
            due_date: document.getElementById('f_due').value,
        };
        if (!data.book_id || !data.member_id || !data.due_date) return toast('All fields required', 'error');
        const r = await api('borrowings.php', 'POST', data);
        if (r.success) { toast('Book issued!'); closeModal(); loadBorrowings(); loadDashboard(); }
        else toast(r.error || 'Error', 'error');
    }
}

async function deleteBook(id, title) {
    if (!confirm(`Delete "${title}"?`)) return;
    const r = await api(`books.php?id=${id}`, 'DELETE');
    if (r.success) { toast('Book deleted'); loadBooks(); }
    else toast(r.error || 'Error', 'error');
}

async function deleteMember(id, name) {
    if (!confirm(`Delete member "${name}"?`)) return;
    const r = await api(`members.php?id=${id}`, 'DELETE');
    if (r.success) { toast('Member deleted'); loadMembers(); }
    else toast(r.error || 'Error', 'error');
}

async function returnBook(id) {
    if (!confirm('Mark this book as returned?')) return;
    const r = await api('borrowings.php', 'PUT', { id });
    if (r.success) { toast('Book returned!'); loadBorrowings(); loadDashboard(); }
    else toast(r.error || 'Error', 'error');
}

// Forms
function bookForm(b = {}) {
    return `
        <div class="form-row">
            <div class="form-group"><label>Title *</label><input id="f_title" type="text" value="${esc(b.title||'')}" placeholder="Book title"></div>
            <div class="form-group"><label>Author *</label><input id="f_author" type="text" value="${esc(b.author||'')}" placeholder="Author name"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>ISBN</label><input id="f_isbn" type="text" value="${esc(b.isbn||'')}" placeholder="978-..."></div>
            <div class="form-group"><label>Genre</label><input id="f_genre" type="text" value="${esc(b.genre||'')}" placeholder="Fiction, Science..."></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Total Copies</label><input id="f_copies" type="number" min="1" value="${b.total_copies||1}"></div>
            <div class="form-group"><label>Published Year</label><input id="f_year" type="number" min="1000" max="2099" value="${b.published_year||''}"></div>
        </div>`;
}

function memberForm(m = {}) {
    return `
        <div class="form-row">
            <div class="form-group"><label>Name *</label><input id="f_name" type="text" value="${esc(m.name||'')}" placeholder="Full name"></div>
            <div class="form-group"><label>Email *</label><input id="f_email" type="email" value="${esc(m.email||'')}" placeholder="email@example.com"></div>
        </div>
        <div class="form-group"><label>Phone</label><input id="f_phone" type="text" value="${esc(m.phone||'')}" placeholder="555-0100"></div>
        <div class="form-group"><label>Address</label><textarea id="f_address" rows="2" placeholder="Street, City">${esc(m.address||'')}</textarea></div>
        ${m.id ? `<div class="form-group"><label>Status</label><select id="f_status"><option value="active" ${m.status==='active'?'selected':''}>Active</option><option value="inactive" ${m.status==='inactive'?'selected':''}>Inactive</option></select></div>` : ''}`;
}

function borrowingForm() {
    const today = new Date();
    const due = new Date(today); due.setDate(due.getDate() + 14);
    const fmt = d => d.toISOString().split('T')[0];
    return `
        <div class="form-group"><label>Book *</label><select id="f_book"><option value="">Loading books...</option></select></div>
        <div class="form-group"><label>Member *</label><select id="f_member"><option value="">Loading members...</option></select></div>
        <div class="form-row">
            <div class="form-group"><label>Borrow Date</label><input type="date" value="${fmt(today)}" disabled style="opacity:0.6"></div>
            <div class="form-group"><label>Due Date *</label><input id="f_due" type="date" value="${fmt(due)}" min="${fmt(today)}"></div>
        </div>`;
}

async function populateBorrowingSelects() {
    const [books, members] = await Promise.all([api('books.php'), api('members.php')]);
    const bSelect = document.getElementById('f_book');
    const mSelect = document.getElementById('f_member');
    if (!bSelect || !mSelect) return;
    
    bSelect.innerHTML = '<option value="">Select a book</option>' + 
        books.filter(b => b.available_copies > 0).map(b => `<option value="${b.id}">${esc(b.title)} (${b.available_copies} avail.)</option>`).join('');
    
    mSelect.innerHTML = '<option value="">Select a member</option>' + 
        members.filter(m => m.status === 'active').map(m => `<option value="${m.id}">${esc(m.name)}</option>`).join('');
}

// Helpers
function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function statusBadge(s) {
    const map = { borrowed:'badge-warning', returned:'badge-success', overdue:'badge-danger' };
    return `<span class="badge ${map[s]||'badge-muted'}">${s}</span>`;
}

// Init
loadDashboard();
</script>
</body>
</html>
