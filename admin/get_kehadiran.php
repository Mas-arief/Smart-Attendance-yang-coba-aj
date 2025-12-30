<?php
/**
 * Model Kehadiran
 * File: models/Kehadiran.php
 */

class Kehadiran {
    private $conn;
    private $table_name = "kehadiran";

    // Properti kehadiran
    public $id_kehadiran;
    public $id_jadwal;
    public $id_mahasiswa;
    public $pertemuan_ke;
    public $tanggal;
    public $status;
    public $keterangan;
    public $waktu_absen;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Rekap kehadiran mahasiswa berdasarkan mahasiswa dan tahun ajaran
     */
    public function getRekapKehadiranMahasiswa($id_mahasiswa, $id_tahun = null) {
        $query = "SELECT 
                    mk.kode_mk,
                    mk.nama_mk,
                    jr.jenis,
                    k.pertemuan_ke,
                    k.status,
                    k.tanggal,
                    k.waktu_absen
                  FROM " . $this->table_name . " k
                  INNER JOIN jadwal_ruangan jr ON k.id_jadwal = jr.id_jadwal
                  INNER JOIN matakuliah mk ON jr.id_matakuliah = mk.id_matakuliah
                  WHERE k.id_mahasiswa = ?";
        
        if ($id_tahun !== null) {
            $query .= " AND jr.id_tahun = ?";
        }
        
        $query .= " ORDER BY mk.kode_mk ASC, k.pertemuan_ke ASC";
        
        $stmt = $this->conn->prepare($query);
        
        if ($id_tahun !== null) {
            $stmt->bindParam(1, $id_mahasiswa);
            $stmt->bindParam(2, $id_tahun);
        } else {
            $stmt->bindParam(1, $id_mahasiswa);
        }
        
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Statistik kehadiran mahasiswa
     */
    public function getStatistikKehadiranMahasiswa($id_mahasiswa, $id_tahun = null) {
        $query = "SELECT 
                    COUNT(CASE WHEN k.status = 'Hadir' THEN 1 END) as total_hadir,
                    COUNT(CASE WHEN k.status = 'Izin' THEN 1 END) as total_izin,
                    COUNT(CASE WHEN k.status = 'Sakit' THEN 1 END) as total_sakit,
                    COUNT(CASE WHEN k.status = 'Alfa' THEN 1 END) as total_alfa,
                    COUNT(*) as total_pertemuan
                  FROM " . $this->table_name . " k
                  INNER JOIN jadwal_ruangan jr ON k.id_jadwal = jr.id_jadwal
                  WHERE k.id_mahasiswa = ?";
        
        if ($id_tahun !== null) {
            $query .= " AND jr.id_tahun = ?";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($id_tahun !== null) {
            $stmt->bindParam(1, $id_mahasiswa);
            $stmt->bindParam(2, $id_tahun);
        } else {
            $stmt->bindParam(1, $id_mahasiswa);
        }
        
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Rekap kehadiran per mata kuliah
     */
    public function getRekapPerMatakuliah($id_mahasiswa, $id_tahun = null, $kode_mk = null) {
        $query = "SELECT 
                    mk.kode_mk,
                    mk.nama_mk,
                    jr.jenis,
                    k.pertemuan_ke,
                    k.status,
                    k.tanggal,
                    k.waktu_absen
                  FROM " . $this->table_name . " k
                  INNER JOIN jadwal_ruangan jr ON k.id_jadwal = jr.id_jadwal
                  INNER JOIN matakuliah mk ON jr.id_matakuliah = mk.id_matakuliah
                  WHERE k.id_mahasiswa = ?";
        
        if ($id_tahun !== null) {
            $query .= " AND jr.id_tahun = ?";
        }
        
        if ($kode_mk !== null) {
            $query .= " AND mk.kode_mk = ?";
        }
        
        $query .= " ORDER BY k.pertemuan_ke ASC";
        
        $stmt = $this->conn->prepare($query);
        
        $param_index = 1;
        $stmt->bindParam($param_index++, $id_mahasiswa);
        
        if ($id_tahun !== null) {
            $stmt->bindParam($param_index++, $id_tahun);
        }
        
        if ($kode_mk !== null) {
            $stmt->bindParam($param_index++, $kode_mk);
        }
        
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Ambil data kehadiran terstruktur untuk tabel (14 minggu)
     */
    public function getRekapTerstruktur($id_mahasiswa, $id_tahun) {
        $query = "SELECT 
                    mk.kode_mk,
                    mk.nama_mk,
                    jr.jenis,
                    jr.id_jadwal,
                    GROUP_CONCAT(
                        CONCAT(k.pertemuan_ke, ':', k.status) 
                        ORDER BY k.pertemuan_ke 
                        SEPARATOR '|'
                    ) as kehadiran_data
                  FROM jadwal_ruangan jr
                  INNER JOIN matakuliah mk ON jr.id_matakuliah = mk.id_matakuliah
                  LEFT JOIN " . $this->table_name . " k ON jr.id_jadwal = k.id_jadwal 
                            AND k.id_mahasiswa = ?
                  WHERE jr.id_tahun = ?
                  GROUP BY mk.kode_mk, mk.nama_mk, jr.jenis, jr.id_jadwal
                  ORDER BY mk.kode_mk ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_mahasiswa);
        $stmt->bindParam(2, $id_tahun);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Input kehadiran
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  (id_jadwal, id_mahasiswa, pertemuan_ke, tanggal, status, keterangan, waktu_absen)
                  VALUES (:id_jadwal, :id_mahasiswa, :pertemuan_ke, :tanggal, :status, :keterangan, :waktu_absen)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->keterangan = htmlspecialchars(strip_tags($this->keterangan));
        
        // Bind values
        $stmt->bindParam(':id_jadwal', $this->id_jadwal);
        $stmt->bindParam(':id_mahasiswa', $this->id_mahasiswa);
        $stmt->bindParam(':pertemuan_ke', $this->pertemuan_ke);
        $stmt->bindParam(':tanggal', $this->tanggal);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':keterangan', $this->keterangan);
        $stmt->bindParam(':waktu_absen', $this->waktu_absen);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Update kehadiran
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET status = :status,
                      keterangan = :keterangan,
                      waktu_absen = :waktu_absen
                  WHERE id_kehadiran = :id_kehadiran";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->keterangan = htmlspecialchars(strip_tags($this->keterangan));
        
        // Bind values
        $stmt->bindParam(':id_kehadiran', $this->id_kehadiran);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':keterangan', $this->keterangan);
        $stmt->bindParam(':waktu_absen', $this->waktu_absen);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Hapus kehadiran
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_kehadiran = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id_kehadiran);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Cek apakah kehadiran sudah ada
     */
    public function isExist($id_jadwal, $id_mahasiswa, $pertemuan_ke) {
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->table_name . "
                  WHERE id_jadwal = ? AND id_mahasiswa = ? AND pertemuan_ke = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_jadwal);
        $stmt->bindParam(2, $id_mahasiswa);
        $stmt->bindParam(3, $pertemuan_ke);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'] > 0;
    }

    /**
     * Ambil daftar mahasiswa yang belum absen
     */
    public function getMahasiswaBelumAbsen($id_jadwal, $pertemuan_ke) {
        $query = "SELECT m.id_mahasiswa, m.nim, m.nama_mahasiswa
                  FROM mahasiswa m
                  WHERE m.id_mahasiswa NOT IN (
                      SELECT id_mahasiswa 
                      FROM " . $this->table_name . "
                      WHERE id_jadwal = ? AND pertemuan_ke = ?
                  )
                  ORDER BY m.nama_mahasiswa ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_jadwal);
        $stmt->bindParam(2, $pertemuan_ke);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Persentase kehadiran mahasiswa
     */
    public function getPersentaseKehadiran($id_mahasiswa, $id_tahun = null) {
        $query = "SELECT 
                    ROUND((COUNT(CASE WHEN k.status = 'Hadir' THEN 1 END) * 100.0 / COUNT(*)), 1) as persentase_hadir,
                    ROUND((COUNT(CASE WHEN k.status = 'Izin' THEN 1 END) * 100.0 / COUNT(*)), 1) as persentase_izin,
                    ROUND((COUNT(CASE WHEN k.status = 'Sakit' THEN 1 END) * 100.0 / COUNT(*)), 1) as persentase_sakit,
                    ROUND((COUNT(CASE WHEN k.status = 'Alfa' THEN 1 END) * 100.0 / COUNT(*)), 1) as persentase_alfa
                  FROM " . $this->table_name . " k
                  INNER JOIN jadwal_ruangan jr ON k.id_jadwal = jr.id_jadwal
                  WHERE k.id_mahasiswa = ?";
        
        if ($id_tahun !== null) {
            $query .= " AND jr.id_tahun = ?";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if ($id_tahun !== null) {
            $stmt->bindParam(1, $id_mahasiswa);
            $stmt->bindParam(2, $id_tahun);
        } else {
            $stmt->bindParam(1, $id_mahasiswa);
        }
        
        $stmt->execute();
        
        return $stmt;
    }
}
?>