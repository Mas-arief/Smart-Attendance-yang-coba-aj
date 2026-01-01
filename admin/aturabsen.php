<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jadwal Ruangan - Admin Polibatam</title>

  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

  <!-- MDB UI Kit -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.css" rel="stylesheet">

  <style>
    body {
      background: #f7f7f7;
      font-family: 'Poppins', sans-serif;
    }

    .content {
      margin-left: 230px;
      margin-top: 90px;
      padding: 30px;
    }

    .table-container {
      background: #fff;
      border-radius: 16px;
      padding: 25px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, .1);
      max-width: 1000px;
      margin: auto;
    }

    h3 {
      color: #173a9b;
      font-weight: 600;
    }

    thead tr {
      background: #eaeaea;
    }

    th {
      font-size: 13px;
      text-transform: uppercase;
    }

    td,
    th {
      padding: 12px 16px;
      border-bottom: 1px solid #ddd;
    }

    tr:hover {
      background: #f9f9f9;
    }

    /* ===== WAKTU STACKED ===== */
    .jadwal-waktu {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .jadwal-hari {
      font-size: 13px;
      font-weight: 600;
      color: #173a9b;
    }

    .jadwal-jam {
      font-size: 14px;
      color: #333;
    }

    /* ===== AKSI ===== */
    .action-icons i {
      cursor: pointer;
      margin: 0 6px;
      font-size: 16px;
      transition: .2s;
    }

    .edit-icon {
      color: #f4b400;
    }

    .edit-icon:hover {
      transform: scale(1.2);
      color: #d39e00;
    }

    .delete-icon {
      color: #e53935;
    }

    .delete-icon:hover {
      transform: scale(1.2);
      color: #b71c1c;
    }

    @media (max-width: 991px) {
      .content {
        margin-left: 0;
        margin-top: 120px;
      }
    }
  </style>
</head>

<body>

  <?php include 'navsideadmin.php'; ?>

  <div class="content">
    <div class="table-container">

      <!-- HEADER -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Jadwal Ruangan</h3>
        <button class="btn text-white" style="background:#173a9b"
          data-mdb-toggle="modal"
          data-mdb-target="#jadwalModal"
          onclick="resetForm()">
          <i class="fa fa-plus"></i> Tambah Jadwal
        </button>
      </div>

      <!-- TABLE -->
      <table class="table align-middle">
        <thead>
          <tr>
            <th>No</th>
            <th>Ruangan</th>
            <th>Mata Kuliah</th>
            <th>Waktu</th>
            <th>Mahasiswa</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="tableBody">
          <tr>
            <td colspan="6" class="text-center text-muted">
              <i class="fa fa-spinner fa-spin"></i> Memuat data...
            </td>
          </tr>
        </tbody>
      </table>

    </div>
  </div>

  <!-- MODAL -->
  <div class="modal fade" id="jadwalModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content p-3">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Tambah Jadwal</h5>
          <button class="btn-close" data-mdb-dismiss="modal"></button>
        </div>

        <form id="formJadwal">
          <div class="modal-body">
            <input type="hidden" id="rowIndex">

            <div class="mb-3">
              <label class="form-label">Ruangan</label>
              <input type="text" id="ruangan" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Mata Kuliah</label>
              <input type="text" id="matkul" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Hari</label>
              <select id="hari" class="form-select">
                <option value="">Pilih Hari</option>
                <option>Senin</option>
                <option>Selasa</option>
                <option>Rabu</option>
                <option>Kamis</option>
                <option>Jumat</option>
                <option>Sabtu</option>
              </select>
            </div>

            <div class="row">
              <div class="col">
                <label class="form-label">Jam Mulai</label>
                <input type="time" id="jamMulai" class="form-control" required>
              </div>
              <div class="col">
                <label class="form-label">Jam Selesai</label>
                <input type="time" id="jamSelesai" class="form-control" required>
              </div>
            </div>

            <div class="mt-3">
              <label class="form-label">Jumlah Mahasiswa</label>
              <input type="number" id="jumlahMhs" class="form-control" min="1" required>
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn btn-secondary" data-mdb-dismiss="modal">Batal</button>
            <button class="btn text-white" style="background:#173a9b">
              <i class="fa fa-save"></i> Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.js"></script>

  <script>
    const tableBody = document.getElementById("tableBody");
    const form = document.getElementById("formJadwal");
    let editIndex = null;

    form.addEventListener("submit", e => {
      e.preventDefault();

      const data = {
        ruangan: ruangan.value,
        matkul: matkul.value,
        hari: hari.value || "-",
        jam: `${jamMulai.value} - ${jamSelesai.value}`,
        mhs: jumlahMhs.value
      };

      if (editIndex !== null) {
        updateRow(editIndex, data);
      } else {
        addRow(data);
      }

      resetForm();
      mdb.Modal.getInstance(document.getElementById("jadwalModal")).hide();
    });

    function addRow(d) {
      const row = tableBody.insertRow();
      row.innerHTML = renderRow(tableBody.rows.length, d);
    }

    function updateRow(i, d) {
      tableBody.rows[i].innerHTML = renderRow(i + 1, d);
    }

    function renderRow(no, d) {
      return `
    <td>${no}</td>
    <td>${d.ruangan}</td>
    <td>${d.matkul}</td>
    <td>
      <div class="jadwal-waktu">
        <div class="jadwal-hari">${d.hari}</div>
        <div class="jadwal-jam">${d.jam}</div>
      </div>
    </td>
    <td>${d.mhs} mahasiswa</td>
    <td class="action-icons">
      <i class="fa fa-pen edit-icon" onclick="editRow(this)"></i>
      <i class="fa fa-trash delete-icon" onclick="deleteRow(this)"></i>
    </td>`;
    }

    function editRow(el) {
      const row = el.closest("tr");
      editIndex = row.rowIndex - 1;

      ruangan.value = row.cells[1].innerText;
      matkul.value = row.cells[2].innerText;
      hari.value = row.querySelector(".jadwal-hari").innerText;

      const jam = row.querySelector(".jadwal-jam").innerText.split(" - ");
      jamMulai.value = jam[0];
      jamSelesai.value = jam[1];

      jumlahMhs.value = parseInt(row.cells[4].innerText);

      document.getElementById("modalTitle").innerText = "Edit Jadwal";
      new mdb.Modal(document.getElementById("jadwalModal")).show();
    }

    function deleteRow(el) {
      if (confirm("Hapus jadwal ini?")) {
        el.closest("tr").remove();
        renumber();
      }
    }

    function renumber() {
      [...tableBody.rows].forEach((r, i) => r.cells[0].innerText = i + 1);
    }

    function resetForm() {
      form.reset();
      editIndex = null;
      document.getElementById("modalTitle").innerText = "Tambah Jadwal";
    }
  </script>

</body>

</html>