var fromClick = !1;
$(function () {
    function a() {
        b();
        var a = $("body").height(), c = $(window).height();
        c > a && $("body,html").css("height", "100%")
    }

    function b() {
        var a = $("#dlBox"), b = $(".header"), c = b.height(), d = $("#top"), e = $("#nav").find("ul li"), f = $("#logoLarge").find("img"), g = f.height(), h = $("#logoMenu"), i = $("#qrCode").find("img"), j = i.height(), k = $("#dlMenu"), l = location.href.split(".html")[0].split("/").slice(-1)[0];
        "suppliers" != l ? $(window).scroll(function () {
            var a = $(this).scrollTop(), b = h.is(":visible"), e = k.is(":visible");
            a + 60 >= f.offset().top + g && !b && (h.fadeIn(), "join" == l && k.fadeIn()), a + 60 < f.offset().top + g && b && ("join" == l && k.fadeOut()), i.length && a + 60 >= i.offset().top + j && !e && k.fadeIn(), i.length && a + 60 < i.offset().top + j && e && k.fadeOut(), a + 60 >= c ? d.addClass("whiteBg") : d.removeClass("whiteBg")
        }) : d.css({position: "static"}), e.hover(function () {
            return $(this).find("a").hasClass("active") ? !1 : void $(this).find("a").animate({marginTop: 2, color: "#ef7521"}, function () {
                $(this).css("borderBottomWidth", 1)
            })
        }, function () {
            return $(this).find("a").hasClass("active") ? !1 : void $(this).find("a").animate({marginTop: 14, color: "#4d4d4d", borderBottomWidth: 0}, 200, function () {
                $(this).removeAttr("style")
            })
        }), e.click(function () {
            if ($(this).find("a").hasClass("active"))return!1;
            if (fromClick = !0, "joinUs" == $(this).attr("id"))return window.location.href = "join.html", !1;
            $(this).siblings().find("a").removeClass("active").removeAttr("style"), $(this).find("a").toggleClass("active");
            var a = e.find(".active").attr("data-href"), b = $("." + a).offset().top;
            $("html,body").animate({scrollTop: b}, 800, function () {
                fromClick = !1
            })
        }), k.hover(function () {
            a.show()
        }).mouseleave(function (b) {
            var c = !1;
            return $(b.toElement).parents(".dlBox").length > 0 && (c = !0), b.toElement && "blBox" == b.toElement.id || c ? !1 : void a.hide()
        }), a.mouseleave(function () {
            a.hide()
        })
    }

    a()
});