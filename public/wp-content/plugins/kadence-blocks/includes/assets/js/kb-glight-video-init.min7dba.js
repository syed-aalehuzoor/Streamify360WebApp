(function(){"use strict";var a={lightboxes:{},foundClasses:{},initAll(){const b=document.querySelectorAll(".ktblocksvideopop");if(b.length&&b)for(let c=0;c<b.length;c++)b[c].setAttribute("role","button"),b[c].setAttribute("aria-haspopup","dialog"),a.foundClasses[c]="kb-video-pop-"+c,b[c].classList.add("kb-video-pop-"+c),a.lightboxes[c]=new GLightbox({selector:"."+a.foundClasses[c],touchNavigation:!0,skin:"kadence-dark",loop:!1,openEffect:"fade",closeEffect:"fade",autoplayVideos:!0,autofocusVideos:!0,plyr:{css:kadence_video_pop.plyr_css,js:kadence_video_pop.plyr_js,config:{muted:!1,hideControls:!0},controls:["play-large","play","progress","current-time","mute","volume","settings","fullscreen"]}}),a.lightboxes[c].on("slide_before_load",()=>{document.querySelector(".gclose.gbtn").focus()}),a.lightboxes[c].on("close",()=>{b[c].focus()})},init(){if("function"==typeof GLightbox)a.initAll();else var b=setInterval(function(){"function"==typeof GLightbox&&(a.initAll(),clearInterval(b))},200)}};"loading"===document.readyState?document.addEventListener("DOMContentLoaded",a.init):a.init()})();