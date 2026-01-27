<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Organization Settings</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f6f8;
    margin: 0;
}

.container {
    display: flex;
    min-height: 100vh;
}

.sidebar {
    width: 260px;
    background: #1f2937;
    color: #fff;
    padding: 20px;
}

.sidebar h2 {
    font-size: 18px;
    margin-bottom: 20px;
}

.sidebar button {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    border: none;
    background: #374151;
    color: #fff;
    cursor: pointer;
    text-align: left;
}

.sidebar button:hover {
    background: #4b5563;
}

.content {
    flex: 1;
    padding: 30px;
}

.card {
    background: #fff;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 6px;
}

.card h3 {
    margin-top: 0;
}

input, select {
    width: 100%;
    padding: 8px;
    margin: 6px 0 12px;
}

button.primary {
    background: #2563eb;
    color: #fff;
    padding: 10px 16px;
    border: none;
    cursor: pointer;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

table th, table td {
    border: 1px solid #ddd;
    padding: 8px;
}

.hidden {
    display: none;
}
</style>
</head>

<body>
<div class="container">

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>⚙ Settings</h2>
    <button onclick="showSection('org')">Organization</button>
    <button onclick="showSection('gst')">GST Registrations</button>
    <button onclick="showSection('domain')">Business Domains</button>
    <button onclick="showSection('service')">Services (SAC/HSN)</button>
</div>

<!-- CONTENT -->
<div class="content">

<!-- ORGANIZATION -->
<div id="org" class="card">
<h3>Organization Profile</h3>

<label>Company Name</label>
<input id="org_name">

<label>Legal Type</label>
<select id="legal_type">
    <option>Pvt Ltd</option>
    <option>Ltd</option>
    <option>Proprietorship</option>
</select>

<label>PAN</label>
<input id="pan" placeholder="AABCT1234K">

<label>CIN</label>
<input id="cin" placeholder="U74999KA2019PTC123456">

<button class="primary" onclick="saveOrganization()">Save Organization</button>
</div>

<!-- GST -->
<div id="gst" class="card hidden">
<h3>GST Registrations</h3>

<label>State Code</label>
<input id="gst_state" placeholder="KA / TN">

<label>GSTIN</label>
<input id="gstin">

<button class="primary" onclick="addGST()">Add GSTIN</button>

<table>
<thead>
<tr><th>State</th><th>GSTIN</th></tr>
</thead>
<tbody id="gstTable"></tbody>
</table>
</div>

<!-- DOMAINS -->
<div id="domain" class="card hidden">
<h3>Business Domains</h3>

<label>Domain Name</label>
<input id="domain_name">

<button class="primary" onclick="addDomain()">Add Domain</button>

<table>
<thead>
<tr><th>Domain</th></tr>
</thead>
<tbody id="domainTable"></tbody>
</table>
</div>

<!-- SERVICES -->
<div id="service" class="card hidden">
<h3>Services (SAC / HSN)</h3>

<label>Domain</label>
<select id="service_domain"></select>

<label>Service Name</label>
<input id="service_name">

<label>SAC / HSN</label>
<input id="sac">

<label>GST %</label>
<input id="gst_percent">

<button class="primary" onclick="addService()">Add Service</button>

<table>
<thead>
<tr><th>Domain</th><th>Service</th><th>SAC</th><th>CGST %</th><th>SGST</th></tr>
</thead>
<tbody id="serviceTable"></tbody>
</table>
</div>

</div>
</div>

<script>
let org = JSON.parse(localStorage.getItem("org")) || {};
let gstList = JSON.parse(localStorage.getItem("gst")) || [];
let domains = JSON.parse(localStorage.getItem("domains")) || [];
let services = JSON.parse(localStorage.getItem("services")) || [];

function showSection(id){
    document.querySelectorAll(".card").forEach(c=>c.classList.add("hidden"));
    document.getElementById(id).classList.remove("hidden");
}

function saveOrganization(){
    org = {
        name: org_name.value,
        legal: legal_type.value,
        pan: pan.value,
        cin: cin.value
    };
    localStorage.setItem("org", JSON.stringify(org));
    alert("Organization saved");
}

function addGST(){
    gstList.push({
        state: gst_state.value,
        gstin: gstin.value
    });
    localStorage.setItem("gst", JSON.stringify(gstList));
    renderGST();
}

function renderGST(){
    gstTable.innerHTML = "";
    gstList.forEach(g=>{
        gstTable.innerHTML += `<tr><td>${g.state}</td><td>${g.gstin}</td></tr>`;
    });
}

function addDomain(){
    domains.push(domain_name.value);
    localStorage.setItem("domains", JSON.stringify(domains));
    renderDomains();
}

function renderDomains(){
    domainTable.innerHTML = "";
    service_domain.innerHTML = "";
    domains.forEach(d=>{
        domainTable.innerHTML += `<tr><td>${d}</td></tr>`;
        service_domain.innerHTML += `<option>${d}</option>`;
    });
}

function addService(){
    services.push({
        domain: service_domain.value,
        name: service_name.value,
        sac: sac.value,
        gst: gst_percent.value
    });
    localStorage.setItem("services", JSON.stringify(services));
    renderServices();
}

function renderServices(){
    serviceTable.innerHTML = "";
    services.forEach(s=>{
        serviceTable.innerHTML +=
        `<tr>
            <td>${s.domain}</td>
            <td>${s.name}</td>
            <td>${s.sac}</td>
            <td>${s.gst}%</td>
        </tr>`;
    });
}

renderGST();
renderDomains();
renderServices();
</script>

</body>
</html>
