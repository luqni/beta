(function(e){var t={};function n(r){if(t[r]){return t[r].exports}var i=t[r]={i:r,l:false,exports:{}};e[r].call(i.exports,i,i.exports,n);i.l=true;return i.exports}n.m=e;n.c=t;n.d=function(e,t,r){if(!n.o(e,t)){Object.defineProperty(e,t,{enumerable:true,get:r})}};n.r=function(e){if(typeof Symbol!=="undefined"&&Symbol.toStringTag){Object.defineProperty(e,Symbol.toStringTag,{value:"Module"})}Object.defineProperty(e,"__esModule",{value:true})};n.t=function(e,t){if(t&1)e=n(e);if(t&8)return e;if(t&4&&typeof e==="object"&&e&&e.__esModule)return e;var r=Object.create(null);n.r(r);Object.defineProperty(r,"default",{enumerable:true,value:e});if(t&2&&typeof e!="string")for(var i in e)n.d(r,i,function(t){return e[t]}.bind(null,i));return r};n.n=function(e){var t=e&&e.__esModule?function t(){return e["default"]}:function t(){return e};n.d(t,"a",t);return t};n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)};n.p=".";return n(n.s=0)})({"./src/parallax.css":function(e,t){},"./src/parallax.js":function(e,t){window.vcv.on("ready",function(e,t){if(e!=="merge"){setTimeout(function(){var e="[data-vce-assets-parallax]";e=t?'[data-vcv-element="'+t+'"] '+e:e;window.vceAssetsParallax(e)},10)}})},"./src/plugin.js":function(e,t){(function(e,t){function n(t){var n={element:null,bgElement:null,waypoint:null,observer:null,reverse:false,speed:30,setup:function e(t){this.resize=this.resize.bind(this);this.handleAttributeChange=this.handleAttributeChange.bind(this);if(!t.getVceParallax){t.getVceParallax=this;this.element=t;this.bgElement=t.querySelector(t.dataset.vceAssetsParallax);this.prepareElement();this.create()}else{this.update()}return t.getVceParallax},handleAttributeChange:function e(){if(this.element.getAttribute("data-vce-assets-parallax")){this.update()}else{this.destroy()}},addScrollEvent:function t(){e.addEventListener("scroll",this.resize);this.resize()},removeScrollEvent:function t(){e.removeEventListener("scroll",this.resize)},resize:function t(){if(!this.element.clientHeight){return}var n=e.innerHeight;var r=this.element.getBoundingClientRect();var i=r.height+n;var l=(r.top-n)*-1;var s=0;if(l>=0&&l<=i){s=l/i}var a=this.speed*2*s*-1+this.speed;if(this.reverse==="true"){a=a*-1}this.bgElement.style.transform="translateY("+a+"vh)"},prepareElement:function e(){var n=parseInt(t.dataset.vceAssetsParallaxSpeed);if(n){this.speed=n}if("vceAssetsParallaxReverse"in t.dataset){this.reverse=t.dataset.vceAssetsParallaxReverse}this.bgElement.style.top="-"+this.speed+"vh";this.bgElement.style.bottom="-"+this.speed+"vh"},create:function e(){var t=this;this.waypoint={};this.waypoint.top=new Waypoint({element:t.element,handler:function e(n){if(n==="up"){t.removeScrollEvent()}if(n==="down"){t.addScrollEvent()}},offset:"100%"});this.waypoint.bottom=new Waypoint({element:t.element,handler:function e(n){if(n==="up"){t.addScrollEvent()}if(n==="down"){t.removeScrollEvent()}},offset:function e(){return-t.element.clientHeight}});t.observer=new MutationObserver(this.handleAttributeChange);t.observer.observe(this.element,{attributes:true})},update:function e(){this.prepareElement();this.resize();Waypoint.refreshAll()},destroy:function e(){this.removeScrollEvent();this.bgElement.style.top=null;this.bgElement.style.bottom=null;this.bgElement.style.transform=null;this.bgElement=null;this.waypoint.top.destroy();this.waypoint.bottom.destroy();this.waypoint=null;this.observer.disconnect();this.observer=null;delete this.element.getVceParallax;this.element=null}};return n.setup(t)}var r={init:function e(r){Waypoint.refreshAll();var i=t.querySelectorAll(r);i=[].slice.call(i);i.forEach(function(e){if(!e.getVceParallax){n(e)}else{e.getVceParallax.update()}});if(i.length===1){return i.pop()}return i}};e.vceAssetsParallax=r.init})(window,document)},0:function(e,t,n){n("./src/plugin.js");n("./src/parallax.js");e.exports=n("./src/parallax.css")}});