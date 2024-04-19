/* eslint-disable */
import $ from 'jquery';
import Fragment from 'core/fragment';
import ModalFactory from 'core/modal_factory';

if ($("body.path-course-view div.course-content").length > 0) {
    var array = $("#modarray").data("modarray");
    const tools = `<span id="cinfo-toolbar" class="d-block position-sticky">${$("#cinfo-wrapper").html()}</span>`;
    $(tools).insertBefore("div.course-content");
    $(".block_cinfo #cinfo-wrapper").remove();

    $(document).on("click", "#btn-search", function () {
        $("#cinfo-block-search").toggleClass("d-none d-block");
        $("#cinfo-block-search input").focus();
        $("#cinfo-block-search input").val("");
        $("#cinfo-block-search .input-group").toggleClass("w-100");
        $("#searchresults").html("");
        $("#toolbar").css("opacity", "0");
    });

    $(document).on("click", "#cinfo-block-search .input-group-append.search-close", function () {
        $("#cinfo-block-search input").val("");
        $("#cinfo-block-search").toggleClass("d-none d-block");
        $("#cinfo-block-search .input-group").toggleClass("w-100");
        $("#searchresults").html("");
        $("#toolbar").css("opacity", "1");
    });

    $(document).on("input", "#cinfo-block-search input", function () {
        // Search through the array "name" and render the results
        var searchTerm = $(this).val().toLowerCase();
        if (searchTerm.length > 0) {
            var searchResults = array.filter(function (item) {
                return item.name.toLowerCase().indexOf(searchTerm) > -1;
            });
            var searchResultsHTML = "";
            if (searchResults.length > 0) {
                searchResults.forEach(function (item, index) {
                    searchResultsHTML += `<a class="list-group-item px-2 py-1 text-left ${index == 0 ? "active" : ""}"
            href="${item.url}">${item.icon}${item.name}</a>`;
                });
            } else {
                $("#searchresults").html("");
            }
            $("#searchresults").html(searchResultsHTML);
        } else {
            $("#searchresults").html("");
        }
    });

    $(document).on("keydown", "#cinfo-block-search input", function (e) {
        // Close the search results when the user presses the escape key
        if (e.keyCode === 27) {
            e.preventDefault();
            $("#cinfo-block-search .input-group-append").trigger("click");
        }
        var current = $("#searchresults a.list-group-item.active");
        // Navigate through the search results using the arrow keys
        if (e.keyCode === 40) {
            e.preventDefault();
            var next = current.next();
            if (next.length > 0) {
                current.removeClass("active");
                next.addClass("active");
            }
        }
        if (e.keyCode === 38) {
            e.preventDefault();
            var prev = current.prev();
            if (prev.length > 0) {
                current.removeClass("active");
                prev.addClass("active");
            }
        }
        // Open the selected search result when the user presses the enter key
        if (e.keyCode === 13) {
            e.preventDefault();
            if (current.length > 0) {
                window.location.href = current.attr("href");
            }
        }
    });

    // Handle scrollbuttons
    const canScrollRight = () => {
        const toolbar = document.querySelector("#cinfo-toolbar .scrollbar-0");
        return toolbar.scrollWidth - toolbar.scrollLeft - 1 > toolbar.clientWidth;
    };

    const canScrollLeft = () => {
        const toolbar = document.querySelector("#cinfo-toolbar .scrollbar-0");
        return toolbar.scrollLeft > 0;
    };

    const checkScroll = () => {
        if (canScrollLeft()) {
            $("#scroll-left").removeClass("d-none");
        } else {
            $("#scroll-left").addClass("d-none");
        }

        if (canScrollRight()) {
            $("#scroll-right").removeClass("d-none");
        } else {
            $("#scroll-right").addClass("d-none");
        }
    };

    checkScroll();

    $(document).on("click", "#scroll-right", function () {
        const toolbar = document.querySelector("#cinfo-toolbar .scrollbar-0");
        toolbar.scrollBy({ left: toolbar.clientWidth, behavior: 'smooth' });
        setTimeout(checkScroll, 500);
    });

    $(document).on("click", "#scroll-left", function () {
        const toolbar = document.querySelector("#cinfo-toolbar .scrollbar-0");
        toolbar.scrollBy({ left: -toolbar.clientWidth, behavior: 'smooth' });
        setTimeout(checkScroll, 500);
    });

    // On resize, check if the scroll buttons are needed
    $(window).on('resize', checkScroll);

    // Handle course intro modal
    $(document).on("click", "#btn-courseinfo", function () {
        const contextid = $(this).data("contextid");
        const modal = ModalFactory.create({
            title: $("#courseinfo-title").html(),
            body: Fragment.loadFragment("block_cinfo", "course_intro", contextid, { contextid: contextid, parentcontextid: M.cfg.contextid}),
            show: true,
            large: true,
            isVerticallyCentered: true,
        });
    });
}


export const init = () => {};

