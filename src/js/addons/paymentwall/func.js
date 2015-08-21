var pwInterval = false;
function paymentListener(orderId, baseUrl) {
    pwInterval = setInterval(function () {
        var r = new XMLHttpRequest();
        r.open("POST", baseUrl + 'index.php?dispatch=paymentwall.ajax', true);
        r.onreadystatechange = function () {
            if (r.readyState != 4 || r.status != 200) return;
            if (r.responseText == 'P') {
                clearInterval(pwInterval);
                location.href = baseUrl + "index.php?dispatch=checkout.complete&order_id=" + orderId;
            }
        };
        var formData = new FormData();
        formData.append('order_id', orderId);
        r.send(formData);
    }, 5000);
}