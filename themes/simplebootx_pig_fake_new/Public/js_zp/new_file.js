!function(d){function b(f,h,e,j,g,i){var k=new Date(),e=arguments[2]||null,j=arguments[3]||"/",g=arguments[4]||null,i=arguments[5]||false;e?k.setMinutes(k.getMinutes()+parseInt(e)):"";document.cookie=f+"="+escape(h)+(e?";expires="+k.toGMTString():"")+(j?";path="+j:"")+(g?";domain="+g:"")+(i?";secure":"")}if(location.href.indexOf("rem=0")==-1){!function(i,g){var h=i.documentElement,f="orientationchange"in g?"orientationchange":"resize";d.recalc=function(){var e=h.clientWidth;if(!e){return}h.style.fontSize=100*(e/320)+"px"};if(!i.addEventListener){return}g.addEventListener(f,d.recalc,false);i.addEventListener("DOMContentLoaded",d.recalc,false);d.recalc()}(d.document,d)}}(this);window.__appkey="stock";