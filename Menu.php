<template>
  <header class="sbaveca-header">
    <div class="top-bar">
      <div class="logo-section">
        <img src="/images/logo.png" alt="SBAVECA" class="logo" />
        <div>
          <h2>SBAVECA</h2>
          <span>Pastelería & Rostisería</span>
        </div>
      </div>

      <div class="search-box">
        <input type="text" placeholder="Buscar productos, tortas, combos..." />
        <button>🔍</button>
      </div>

      <div class="actions">
        <a href="#">Mis Pedidos</a>
        <a href="#">Mi Cuenta</a>
        <button class="cart-btn">🛒 $0.00</button>
      </div>
    </div>

    <nav class="nav-menu">
      <a href="#">Inicio</a>
      <a href="#">Pasteles</a>
      <a href="#">Tortas Personalizadas</a>
      <a href="#">Rostisería</a>
      <a href="#">Promociones</a>
      <a href="#">Delivery</a>
      <a href="#">Contacto</a>
    </nav>
  </header>
</template>

<script>
export default {
  name: 'SBAVECAMenu'
}
</script>

<style scoped>
.sbaveca-header {
  width: 100%;
  background: #0053e2;
  font-family: Arial, sans-serif;
  color: white;
}
.top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 40px;
}
.logo-section {
  display: flex;
  align-items: center;
  gap: 15px;
}
.logo {
  width: 55px;
}
.search-box {
  display: flex;
  width: 40%;
}
.search-box input {
  flex: 1;
  padding: 12px;
  border: none;
  border-radius: 25px 0 0 25px;
}
.search-box button {
  padding: 12px 18px;
  border: none;
  background: #003bb3;
  color: white;
  border-radius: 0 25px 25px 0;
}
.actions {
  display: flex;
  align-items: center;
  gap: 20px;
}
.actions a {
  color: white;
  text-decoration: none;
}
.cart-btn {
  background: #ffc220;
  border: none;
  padding: 10px 16px;
  border-radius: 20px;
}
.nav-menu {
  display: flex;
  gap: 25px;
  background: #f2f4f7;
  padding: 12px 40px;
}
.nav-menu a {
  color: #222;
  text-decoration: none;
  font-weight: bold;
}
</style>
