<?php
session_start();
$page = $_GET['page'] ?? 'login';
if(!isset($_SESSION['user']) && $page != 'login'){
    header("Location: system.php?page=login");
    exit();
}
$conn = new mysqli("localhost","root","","inventory_system");


if($page == 'logout'){
    session_destroy();
    header("Location: system.php");
    exit();
}
$msg = "";

/* REGISTER */
if(isset($_POST['register'])){
    $conn->query("INSERT INTO users(username,password) VALUES('{$_POST['username']}','{$_POST['password']}')");
    $msg="Account created!";
}

/* LOGIN */
if(isset($_POST['login'])){
    $res=$conn->query("SELECT * FROM users WHERE username='{$_POST['username']}' AND password='{$_POST['password']}'");
    if($res->num_rows>0){
        $_SESSION['user']=$_POST['username'];
        header("Location: system.php?page=dashboard");
    } else { $msg="Invalid login!"; }
}

/* ADD */
if(isset($_POST['add'])){
    $conn->query("INSERT INTO items(item_name,quantity,price,supplier)
    VALUES('{$_POST['item_name']}','{$_POST['quantity']}','{$_POST['price']}','{$_POST['supplier']}')");
    $msg="Item Added!";
}

/* DELETE */
if(isset($_GET['delete'])){
    $conn->query("DELETE FROM items WHERE id=".$_GET['delete']);
    header("Location: system.php?page=report");
}

/* EDIT */
if(isset($_POST['update'])){
    $conn->query("UPDATE items SET 
    item_name='{$_POST['item_name']}',
    quantity='{$_POST['quantity']}',
    price='{$_POST['price']}',
    supplier='{$_POST['supplier']}'
    WHERE id=".$_POST['id']);
    header("Location: system.php?page=report");
}
/* DELETE ITEM*/
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $conn->query("DELETE FROM items WHERE id=$id");
    header("Location: system.php?page=report");
    exit();
}

/* SEARCH */
$results=[];
if(isset($_POST['search'])){
    $k=$_POST['keyword'];
    $res=$conn->query("SELECT * FROM items WHERE item_name LIKE '%$k%'");
    while($r=$res->fetch_assoc()) $results[]=$r;
}

/* STATS */
$total = $conn->query("SELECT COUNT(*) as c FROM items")->fetch_assoc()['c'];
$low = $conn->query("SELECT COUNT(*) as c FROM items WHERE quantity<20")->fetch_assoc()['c'];

?>


<!DOCTYPE html>
<html>
<head>
<title>Inventory Management System</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{font-family:'Segoe UI';margin:0;background:linear-gradient(135deg,#4facfe,#00f2fe);}
.navbar{background:#111;padding:15px;color:white;display:flex;justify-content:space-between;}
.navbar a{color:white;margin-left:15px;text-decoration:none;}
.card{background:white;padding:25px;border-radius:15px;width:350px;margin:60px auto;text-align:center;box-shadow:0 10px 25px rgba(0,0,0,0.3);}
input,button{width:100%;padding:10px;margin:8px 0;border-radius:6px;border:1px solid #ccc;}
button{background:#667eea;color:white;border:none;}
.table-box{width:90%;margin:30px auto;background:white;padding:20px;border-radius:12px;}
table{width:100%;border-collapse:collapse;}
th{background:#667eea;color:white;}
td,th{padding:10px;text-align:center;}
.low{color:red;font-weight:bold;}
.ok{color:green;font-weight:bold;}
.actions a{margin:0 5px;text-decoration:none;}
</style>

</head>
<body>

<?php if(isset($_SESSION['user'])): ?>
<div class="navbar">
<div>Inventory Management System</div>
<div>
<a href="?page=dashboard">Dashboard</a>
<a href="?page=add">Add</a>
<a href="?page=report">Report</a>
<a href="?page=logout">Logout</a>
</div>
</div>
<?php endif; ?>

<?php if($page=='login' || !isset($_SESSION['user'])): ?>

<div class="card">
<h2>Inventory Management System Login</h2>
<form method="post">
<input name="username" placeholder="Username">
<input type="password" name="password" placeholder="Password">
<button name="login">Login</button>
</form>

<form method="post">
<input name="username" placeholder="New Username">
<input name="password" placeholder="New Password">
<button name="register">Register here if you don't have an account.</button>
</form>

<p style="color:red;"><?php echo $msg; ?></p>
</div>

<?php elseif($page=='dashboard'): ?>

<div class="card">
<h2>Welcome <?php echo $_SESSION['user']; ?></h2>
<p>Total Items: <?php echo $total; ?></p>
<p>Low Stock: <?php echo $low; ?></p>
<canvas id="chart"></canvas>
</div>

<script>
new Chart(document.getElementById('chart'),{
type:'pie',
data:{
labels:['Total','Low'],
datasets:[{data:[<?php echo $total;?>,<?php echo $low;?>]}]
}
});
</script>

<?php elseif($page=='add'): ?>

<div class="card">
<h2>Add Item</h2>
<form method="post">
<input name="item_name" placeholder="Item Name">
<input name="quantity" type="number" placeholder="Quantity">
<input name="price" type="number" placeholder="Price">
<input name="supplier" placeholder="Supplier">
<button name="add">Add</button>
</form>
</div>

<?php elseif($page=='report'): ?>

<div class="table-box">
<h2>Inventory</h2>

<input id="searchBox" placeholder="Live search...">

<table id="table">
<tr><th>ID</th><th>Name</th><th>Qty</th><th>Price</th><th>Status</th><th>Action</th></tr>

<?php
$res=$conn->query("SELECT * FROM items");
while($r=$res->fetch_assoc()):
?>
<tr>
<td><?= $r['id']?></td>
<td><?= $r['item_name']?></td>
<td><?= $r['quantity']?></td>
<td><?= $r['price']?></td>
<td class="<?= ($r['quantity']<10)?'low':'ok' ?>">
<?= ($r['quantity']<10)?'Low':'OK' ?>
</td>

<td class="actions">
<a href="?delete=<?= $r['id']?>" onclick="return confirm('Delete?')">❌</a>
<a href="?page=edit&id=<?= $r['id']?>">✏️</a>
</td>
<td>
<a href="?page=edit&id=<?= $row['id'] ?>">✏️</a> |
<a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this item?')">🗑️</a>
</td>
</tr>
<?php endwhile; ?>
</table>
</div>

<script>
document.getElementById("searchBox").addEventListener("keyup",function(){
let filter=this.value.toLowerCase();
document.querySelectorAll("#table tr").forEach((row,i)=>{
if(i===0) return;
row.style.display=row.innerText.toLowerCase().includes(filter)?"":"none";
});
});
</script>

<?php elseif($page=='edit'): ?>

<?php
$id=$_GET['id'];
$data=$conn->query("SELECT * FROM items WHERE id=$id")->fetch_assoc();
?>

<div class="card">
<h2>Edit Item</h2>
<form method="post">
<input type="hidden" name="id" value="<?= $data['id']?>">
<input name="item_name" value="<?= $data['item_name']?>">
<input name="quantity" value="<?= $data['quantity']?>">
<input name="price" value="<?= $data['price']?>">
<input name="supplier" value="<?= $data['supplier']?>">
<button name="update">Update</button>
</form>
</div>

<?php endif; ?>

</body>
</html>