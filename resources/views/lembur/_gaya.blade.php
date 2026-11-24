<style>
.periksa-lembur-page {
    width: 100%;
    padding: 0 8px 28px;
    color: #101828;
    font-family: 'Segoe UI', Inter, Arial, sans-serif;
}

.periksa-lembur-page * {
    box-sizing: border-box;
}

.pl-card {
    background: #fff;
    border: 1px solid #d0d5dd;
    border-radius: 18px;
}

/* Ringkasan Atas */
.pl-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 20px;
}

.pl-stat-card {
    min-height: 98px;
    padding: 18px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
}

.pl-stat-num {
    font-size: 32px;
    font-weight: 700;
    line-height: 1.15;
    color: #101828;
    margin-bottom: 6px;
}

.pl-stat-lbl {
    font-size: 13px;
    color: #475467;
}

.pl-stat-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    background: #eef2f6;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.pl-stat-icon svg {
    width: 21px;
    height: 21px;
    stroke: #101828;
    stroke-width: 1.8;
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
}

/* Kartu Utama & Detail */
.pl-main-card {
    padding: 24px;
}

.pl-detail-card {
    padding: 22px 24px;
    margin-bottom: 18px;
}

.pl-main-title,
.pl-detail-title {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: #101828;
}

.pl-main-title {
    margin-bottom: 18px;
}

.pl-detail-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 18px;
}

/* Breadcrumb */
.pl-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #667085;
    margin-bottom: 16px;
}

.pl-breadcrumb a {
    color: #667085;
    text-decoration: none;
}

.pl-breadcrumb a:hover {
    color: #101828;
}

.pl-breadcrumb strong {
    color: #101828;
    font-weight: 600;
}

/* Meta Detail */
.pl-meta-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
}

.pl-meta-label {
    font-size: 12.5px;
    color: #667085;
    margin-bottom: 6px;
}

.pl-meta-value {
    font-size: 14px;
    font-weight: 700;
    color: #101828;
}

/* Filter */
.pl-filter-form {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}

.pl-search-box {
    width: 310px;
    max-width: 100%;
    height: 42px;
    border: 1px solid #d0d5dd;
    border-radius: 10px;
    padding: 0 13px;
    display: flex;
    align-items: center;
    gap: 9px;
    background: #fff;
}

.pl-search-box svg {
    width: 17px;
    height: 17px;
    stroke: #667085;
    stroke-width: 2;
    fill: none;
    flex-shrink: 0;
}

.pl-search-box input {
    width: 100%;
    border: 0;
    outline: 0;
    font-size: 13.5px;
    color: #101828;
    background: transparent;
}

.pl-select-box select {
    height: 42px;
    min-width: 155px;
    border: 1px solid #d0d5dd;
    border-radius: 10px;
    padding: 0 34px 0 13px;
    font-size: 13.5px;
    font-weight: 500;
    color: #101828;
    background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='%23344054' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E") no-repeat right 12px center;
    appearance: none;
    cursor: pointer;
    outline: none;
}

/* Tabel */
.pl-table-wrap {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #d0d5dd;
    border-radius: 12px;
}

.pl-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 720px;
}

.pl-table thead {
    background: #f8f9fa;
}

.pl-table th {
    height: 44px;
    padding: 0 14px;
    text-align: left;
    font-size: 13px;
    font-weight: 600;
    color: #344054;
    border-bottom: 1px solid #d0d5dd;
    white-space: nowrap;
}

.pl-table td {
    height: 54px;
    padding: 0 14px;
    font-size: 13.5px;
    color: #101828;
    border-bottom: 1px solid #eef2f6;
    vertical-align: middle;
    white-space: nowrap;
}

.pl-table tbody tr:last-child td {
    border-bottom: 0;
}

.pl-col-no {
    width: 54px;
    color: #667085 !important;
}

.pl-col-tengah {
    text-align: center !important;
}

.pl-col-aksi {
    text-align: right !important;
}

.pl-empty {
    text-align: center !important;
    padding: 28px 14px !important;
    color: #667085 !important;
}

/* Badge Status */
.pl-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 28px;
    padding: 0 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}

.badge-menunggu {
    background: #ffedd5;
    color: #c2410c;
}

.badge-selesai,
.badge-disetujui {
    background: #dcfce7;
    color: #15803d;
}

.badge-dikembalikan {
    background: #fef3c7;
    color: #b45309;
}

.badge-diketahui {
    background: #dbeafe;
    color: #1d4ed8;
}

.badge-ditolak {
    background: #fee2e2;
    color: #b91c1c;
}

/* Tombol Tabel */
.pl-btn-aksi-oren {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 32px;
    padding: 0 16px;
    border-radius: 8px;
    background: #ff941c;
    color: #fff;
    font-size: 12.5px;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.18s ease;
}

.pl-btn-aksi-oren:hover {
    background: #e8850f;
}

.pl-btn-aksi-detail {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 32px;
    padding: 0 15px;
    border-radius: 8px;
    border: 1px solid #d0d5dd;
    background: #fff;
    color: #101828;
    font-size: 12.5px;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.18s ease;
}

.pl-btn-aksi-detail:hover {
    background: #f8fafc;
}

/* Paginasi */
.pl-pagination-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 16px;
    flex-wrap: wrap;
}

.pl-pagination-info {
    font-size: 13px;
    color: #667085;
}

.pl-pagination-links {
    display: flex;
    align-items: center;
    gap: 6px;
}

.pl-page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 34px;
    height: 34px;
    padding: 0 10px;
    border-radius: 8px;
    border: 1px solid #d0d5dd;
    background: #fff;
    color: #344054;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
}

.pl-page-btn:hover:not(.disabled):not(.active) {
    background: #f8fafc;
}

.pl-page-btn.active {
    background: #ff941c;
    border-color: #ff941c;
    color: #fff;
}

.pl-page-btn.disabled {
    color: #98a2b3;
    background: #f9fafb;
    pointer-events: none;
}

/* Form Catatan & Tombol Aksi Detail */
.pl-catatan-label {
    display: block;
    font-size: 13px;
    color: #475467;
    margin-bottom: 10px;
}

.pl-field-label {
    display: block;
    font-size: 13px;
    font-weight: 700;
    color: #101828;
    margin-bottom: 8px;
}

.pl-catatan-textarea {
    width: 100%;
    min-height: 92px;
    border: 1px solid #d0d5dd;
    border-radius: 10px;
    padding: 12px 14px;
    font-size: 13.5px;
    font-family: inherit;
    color: #101828;
    resize: vertical;
    outline: none;
}

.pl-catatan-textarea:focus {
    border-color: #ff941c;
}

.pl-textarea-kecil {
    min-height: 76px;
}

.pl-error {
    margin-top: 6px;
    font-size: 12.5px;
    color: #d92d20;
}

.pl-form-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
    flex-wrap: wrap;
}

.pl-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    height: 40px;
    padding: 0 18px;
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 600;
    font-family: inherit;
    text-decoration: none;
    cursor: pointer;
    transition: background 0.18s ease, border-color 0.18s ease;
}

.pl-btn svg {
    width: 16px;
    height: 16px;
    stroke: currentColor;
    stroke-width: 2.2;
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.pl-btn-sekunder {
    border: 1px solid #d0d5dd;
    background: #fff;
    color: #101828;
}

.pl-btn-sekunder:hover {
    background: #f8fafc;
}

.pl-btn-oren {
    border: 1px solid #ff941c;
    background: #ff941c;
    color: #fff;
}

.pl-btn-oren:hover {
    background: #e8850f;
    border-color: #e8850f;
}

.pl-btn-merah {
    border: 1px solid #d92d20;
    background: #d92d20;
    color: #fff;
}

.pl-btn-merah:hover {
    background: #b42318;
    border-color: #b42318;
}

.pl-btn-hijau {
    border: 1px solid #16a34a;
    background: #16a34a;
    color: #fff;
}

.pl-btn-hijau:hover {
    background: #15803d;
    border-color: #15803d;
}

/* Alur Persetujuan 4 Langkah */
.pl-stepper {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
    position: relative;
}

.pl-step {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.pl-step:not(:last-child)::after {
    content: "";
    position: absolute;
    top: 16px;
    left: calc(50% + 22px);
    width: calc(100% - 44px);
    height: 2px;
    background: #d0d5dd;
}

.pl-step.done:not(:last-child)::after {
    background: #ff941c;
}

.pl-step-circle {
    width: 32px;
    height: 32px;
    border-radius: 999px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 10px;
    position: relative;
    z-index: 1;
}

.pl-step-circle svg {
    width: 15px;
    height: 15px;
    stroke: #fff;
    stroke-width: 2.5;
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.pl-step.done .pl-step-circle {
    background: #ff941c;
    color: #fff;
}

.pl-step.current .pl-step-circle {
    background: #fff;
    border: 2px solid #ff941c;
    color: #ff941c;
}

.pl-step.pending .pl-step-circle {
    background: #f2f4f7;
    border: 1.5px solid #d0d5dd;
    color: #667085;
}

.pl-step-title {
    font-size: 13px;
    font-weight: 700;
    color: #101828;
    margin-bottom: 3px;
}

.pl-step-sub {
    font-size: 11.5px;
    color: #667085;
}

/* Catatan Kepala Produksi */
.pl-note-box {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.pl-note-icon {
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 1px;
}

.pl-note-icon svg {
    width: 18px;
    height: 18px;
    stroke: #101828;
    stroke-width: 1.9;
    fill: none;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.pl-note-title {
    font-size: 13.5px;
    font-weight: 700;
    color: #101828;
    margin-bottom: 4px;
}

.pl-note-text {
    font-size: 13px;
    color: #475467;
    line-height: 1.45;
}

/* Form Tolak */
.pl-tolak-sub {
    margin: 0 0 18px;
    font-size: 13px;
    color: #667085;
}

.pl-tolak-ringkasan {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    padding: 16px 18px;
    border-radius: 12px;
    background: #f2f4f7;
    margin-bottom: 20px;
}

/* Responsif */
@media (max-width: 960px) {
    .pl-stats-grid,
    .pl-meta-grid,
    .pl-tolak-ringkasan {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 720px) {
    .pl-stepper {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .pl-step:not(:last-child)::after {
        display: none;
    }
}

@media (max-width: 600px) {
    .pl-stats-grid,
    .pl-meta-grid,
    .pl-tolak-ringkasan {
        grid-template-columns: 1fr;
    }

    .pl-form-actions {
        justify-content: stretch;
    }

    .pl-form-actions .pl-btn {
        width: 100%;
    }
}
</style>