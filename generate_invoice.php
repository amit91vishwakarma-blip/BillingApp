<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'db.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit();
}

$profile = $conn->query("SELECT * FROM profile LIMIT 1")->fetch_assoc();
$products = $conn->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html>
<head>
<title>Invoice</title>

<style>
body { font-family: Arial; background:#f5f5f5; }

.invoice {
    width: 900px;
    margin: auto;
    background: #fff;
    border: 1px solid #000;
}

table {
    width:100%;
    border-collapse: collapse;
}

td, th {
    border:1px solid #000;
    padding:6px;
    font-size:13px;
}

.header {
    background:#5d88b5;
    color:white;
    text-align:center;
    font-weight:bold;
}

.section {
    background:#e5e88c;
    font-weight:bold;
}

input, select {
    width:100%;
    border:none;
    outline:none;
    background: transparent;
}

.center { text-align:center; }
.right { text-align:right; }

button {
    padding:6px 10px;
    margin:5px;
}
</style>

<script>
function fillRow(sel){
    let row = sel.closest("tr");
    let opt = sel.options[sel.selectedIndex];

    row.querySelector(".hsn").value = opt.dataset.hsn || '';
    row.querySelector(".rate").value = opt.dataset.price || '';
    row.querySelector(".unit").value = opt.dataset.unit || 'Piece';

    calc();
}

function calc(){
    let rows = document.querySelectorAll(".itemRow");
    let total = 0;

    rows.forEach(r=>{
        let qty = parseFloat(r.querySelector(".qty").value)||0;
        let rate = parseFloat(r.querySelector(".rate").value)||0;

        let amt = qty*rate;
        r.querySelector(".amt").value = amt.toFixed(2);

        total += amt;
    });

    document.getElementById("taxable").innerText = total.toFixed(2);

    let cg = parseFloat(document.getElementById("cgst").value)||0;
    let sg = parseFloat(document.getElementById("sgst").value)||0;

    let cgst = total * cg / 100;
    let sgst = total * sg / 100;

    document.getElementById("cgst_amt").innerText = cgst.toFixed(2);
    document.getElementById("sgst_amt").innerText = sgst.toFixed(2);

    let grand = total + cgst + sgst;
    document.getElementById("grand").innerText = grand.toFixed(2);

    document.getElementById("words").innerText = Math.floor(grand) + " Rupees Only";
}

function addRow(){
    let row = document.querySelector(".itemRow").cloneNode(true);
    row.querySelectorAll("input").forEach(i=>i.value="");
    row.querySelector("select").selectedIndex=0;

    document.getElementById("items").appendChild(row);
}
</script>

</head>

<body>

<div class="invoice">

<!-- HEADER -->
<table>
<tr class="header">
<td colspan="7">Tax Invoice</td>
</tr>

<tr>
<td width="20%" class="center">
<?php if(!empty($profile['logo'])){ ?>
<img src="uploads/<?php echo $profile['logo']; ?>" width="90">
<?php } ?>
</td>

<td colspan="3">
<b><?php echo $profile['shop_name']; ?></b><br>
<?php echo $profile['address']; ?><br>
GSTIN: <?php echo $profile['gst']; ?><br>
Phone: <?php echo $profile['mobile']; ?><br>
Email: <?php echo $profile['email']; ?>
</td>

<td colspan="3">
Invoice No <input><br>
Date <input type="date"><br>
Dispatch Doc <input>
</td>
</tr>
</table>

<!-- BUYER -->
<table>
<tr class="section"><td colspan="7">Buyer</td></tr>
<tr><td colspan="7">Name <input></td></tr>
<tr><td colspan="7">Address <input></td></tr>
<tr>
<td colspan="3">GSTIN <input></td>
<td colspan="4">State <input></td>
</tr>
<tr><td colspan="7">Phone <input></td></tr>
</table>

<form method="POST">

<!-- ITEMS -->
<table>
<tr class="section center">
<th>S.No</th>
<th>Product</th>
<th>HSN</th>
<th>Qty</th>
<th>Rate</th>
<th>Unit</th>
<th>Amount</th>
</tr>

<tbody id="items">

<tr class="itemRow">
<td>1</td>

<td>
<select name="product[]" onchange="fillRow(this)">
<option>Select</option>
<?php while($p=$products->fetch_assoc()){ ?>
<option data-hsn="<?=$p['hsn']?>" data-price="<?=$p['price']?>" data-unit="<?=$p['unit']?>">
<?=$p['name']?>
</option>
<?php } ?>
</select>
</td>

<td><input name="hsn[]" class="hsn"></td>
<td><input name="qty[]" class="qty" oninput="calc()"></td>
<td><input name="rate[]" class="rate"></td>
<td><input name="unit[]" class="unit"></td>
<td><input name="amount[]" class="amt"></td>

</tr>

</tbody>
</table>

<button type="button" onclick="addRow()">+ Add Row</button>

<br><br>

<!-- TOTAL -->
<table>
<tr>
<td class="right"><b>Taxable</b></td>
<td id="taxable">0.00</td>
</tr>

<tr>
<td>CGST
<select id="cgst" onchange="calc()">
<option value="2.5">2.5%</option>
<option value="6">6%</option>
<option value="9">9%</option>
</select>
</td>
<td id="cgst_amt">0.00</td>
</tr>

<tr>
<td>SGST
<select id="sgst" onchange="calc()">
<option value="2.5">2.5%</option>
<option value="6">6%</option>
<option value="9">9%</option>
</select>
</td>
<td id="sgst_amt">0.00</td>
</tr>

<tr>
<td><b>Total</b></td>
<td id="grand">0.00</td>
</tr>
</table>

<br>

<!-- WORD -->
<table>
<tr>
<td><b>Amount in words:</b> <span id="words"></span></td>
</tr>
</table>

<!-- TERMS -->
<table>
<tr>
<td>
<b>Terms and Conditions</b><br>
1. Goods once sold will not be returned<br>
2. Warranty as per company policy
</td>
</tr>
</table>

<!-- SIGN -->
<table>
<tr>
<td class="center" style="height:80px;">Customer Signature</td>
<td class="center">Authorised Signature</td>
</tr>
</table>

<br>

<button type="submit">Save Invoice</button>
<button type="button" onclick="window.print()">Print</button>

</form>

</div>

</body>
</html>
