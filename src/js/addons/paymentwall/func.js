function paymentListener(orderId, baseUrl) {
    setInterval(function () {
        var r = new XMLHttpRequest();
        r.open("POST", baseUrl + 'index.php?dispatch=paymentwall.ajax', true);
        r.onreadystatechange = function () {
            if (r.readyState != 4 || r.status != 200) return;
            if (r.responseText == 'P') {
                location.href = baseUrl + "index.php?dispatch=checkout.complete&order_id=" + orderId;
            }
        };
        var formData = new FormData();
        formData.append('order_id', orderId);
        r.send(formData);
    }, 5000);
}