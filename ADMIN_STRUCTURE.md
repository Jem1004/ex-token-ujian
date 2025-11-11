# Admin Panel Structure - Token Ujian System

## 📁 **File Hierarchy**

```
token-exujian/
├── admin.php              # ⭐ Referensi Utama (Original)
├── admin-smkmutu.php      # 🎓 SMK MUTU PPU Variant
└── README_ADMIN_STRUCTURE.md
```

---

## 🎯 **Referensi Utama: `admin.php`**

### ✅ **Role:**
- **Master Template** - Struktur dasar admin panel
- **Universal Version** - Dapat digunakan untuk institusi apa pun
- **Reference Standard** - Template untuk admin panel khusus

### 🔧 **Spesifikasi:**
- **Password:** `indonesia2025`
- **Theme:** Hijau (#3498db, #2980b9)
- **Title:** "Token Admin"
- **Database Connection:** Menggunakan config.php
- **Features:**
  - Token generation
  - Rate limit reset
  - Access logs monitoring
  - Auto-regeneration
  - Countdown timer

---

## 🏫 **Variant: `admin-smkmutu.php`**

### ✅ **Role:**
- **Branded Version** - Khusus SMK MUTU PPU
- **Derived Template** - Berdasarkan admin.php
- **Enhanced Design** - Tema biru yang modern

### 🎨 **Customizations:**
- **Password:** `indonesia2025` (sama dengan referensi)
- **Theme:** Biru dan putih (#2563eb, #dbeafe)
- **Title:** "Token Admin - SMK MUTU PPU"
- **Branding:** Logo dan identitas SMK MUTU PPU
- **Features:** Identik dengan admin.php

### 🔗 **Kesamaan dengan Referensi:**
- ✅ Logic & functionality 100% sama
- ✅ Database operations identik
- ✅ JavaScript functions sama
- ✅ Security measures sama
- ✅ Mobile responsive sama

---

## 🔄 **Struktur Kode yang Konsisten**

### **PHP Logic (100% Identik):**
```php
// Authentication
if ($_POST['admin_password'] === 'indonesia2025')

// Token Management
$newToken = createNewToken();
resetRateLimiting($_POST['ip_address']);

// Database Operations
$result = $conn->query("SELECT id, token_value, expires_at, created_at FROM tokens...");
```

### **JavaScript (100% Identik):**
```javascript
// Countdown Timer
function updateCountdown() { ... }

// Auto-refresh Logic
setInterval(function() { ... }, 30000);
```

---

## 🔐 **Security & Password Management**

### **Credentials:**
- **Admin Original:** `indonesia2025`
- **Admin SMK MUTU PPU:** `indonesia2025`

### **Security Features:**
- Session-based authentication
- Rate limiting protection
- Auto-logout on session timeout
- SQL injection prevention
- XSS protection

---

## 📊 **Konsistensi Fitur**

| **Feature** | **admin.php** | **admin-smkmutu.php** | **Status** |
|--------------|--------------|---------------------|-----------|
| Login | ✅ | ✅ | Identik |
| Token Generation | ✅ | ✅ | Identik |
| Rate Limit Reset | ✅ | ✅ | Identik |
| Access Logs | ✅ ✅ | Identik |
| Countdown Timer | ✅ | ✅ | Identik |
| Auto-regeneration | ✅ | ✅ | Identik |
| Mobile Responsive | ✅ | ✅ | Identik |

---

## 🎨 **Perbedaan Visual**

### **Admin.php (Referensi)**
- 🎨 Theme: Hijau
- 📱 Header: "Admin Login"
- 🔵 Token Box: Border hijau
- 🎯 Fokus: Universal

### **Admin SMK MUTU PPU**
- 🎨 Theme: Biru
- 📱 Header: "Admin Login - SMK MUTU PPU"
- 🔵 Token Box: Border biru
- 🎯 Fokus: Branded

---

## 🚀 **Maintenance & Updates**

### **Guidelines:**
1. **Update Logic:** Lakukan di admin.php terlebih dahulu
2. **Testing:** Uji di kedua file
3. **Apply Changes:** Salin perubahan ke admin-smkmutu.php
4. **Visual Only:** Jangan ubah logic di admin-smkmutu.php

### **Workflow:**
```
admin.php (Referensi)
    ↓ [Update Logic]
admin-smkmutu.php (Copy & Visual Adjust)
    ↓ [Visual Customization]
Production Ready
```

---

## 📋 **Usage Guidelines**

### **Untuk Admin Panel Baru:**
1. **Template:** Copy dari admin.php
2. **Branding:** Ubah tema dan identitas visual
3. **Password:** Update sesuai kebutuhan
4. **Testing:** Validasi semua fitur
5. **Documentation:** Tambahkan ke README_ADMIN_STRUCTURE.md

### **Best Practices:**
- ✅ **Referensi Terlebih Dahulu:** admin.php adalah master template
- ✅ **Struktur Kode:** Pertahankan konsistensi
- ✅ **Update Sinkron:** Pastikan perubahan logika diterapkan ke semua variant
- ✅ **Visual Separation:** Pisahkan branding dari logic bisnis

---

## 🎯 **Kesimpulan**

- **admin.php** adalah **referensi utama** dan master template
- **admin-smkmutu.php** adalah **variant branded** yang mengikuti referensi
- **Struktur kode** 100% konsisten antar kedua file
- **Perbedaan** hanya pada visual dan branding
- **Pemeliharaan** dimulai dari referensi utama

📌 **Catatan:** Selalu update referensi utama (admin.php) terlebih dahulu sebelum menerapkan perubahan ke variant lainnya.