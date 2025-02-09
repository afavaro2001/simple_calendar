function hslToHex(h, s, l) {
    l /= 100;
    const a = s * Math.min(l, 1 - l) / 100;
    const f = n => {
        const k = (n + h / 30) % 12;
        const color = l - a * Math.max(Math.min(k - 3, 9 - k, 1), -1);
        return Math.round(255 * color).toString(16).padStart(2, '0');   // convert to Hex and prefix "0" if needed
    };
    return `#${f(0)}${f(8)}${f(4)}`;
}
var F = function (t) {
    valNum = parseFloat(t);
    f = Math.round((valNum - 32) / 1.8);
    var hue = 30 + 240 * (30 - f) / 60;
    var hex = hslToHex(Math.round(hue), 100, 90)
    return hex;
};

function getAppCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(";").shift();
}

const adminEventSource = new EventSource(
    app_config.site_url + "/assets/js-streams/admin.php",
    {
        withCredentials: true,
    }
);

adminEventSource.addEventListener(
    "logout_user",
    (event) => {
        if (getAppCookie("user_id") == event.data) {
            alert("The administrator is logging this user out.");
            window.location.href = app_config.site_url + "?logout=1";
        }
    },
    false
);
