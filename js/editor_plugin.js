﻿(function(){tinymce.PluginManager.requireLangPack("cart66");tinymce.create("tinymce.plugins.cart66",{init:function(ed,url){ed.addCommand("mcephproduct",function(){ed.windowManager.open({file:wpurl+"?cart66dialog=1",width:500,height:255+(tinyMCE.isNS7?20:0)+(tinyMCE.isMSIE?0:0),inline:1},{plugin_url:url,some_custom_arg:"custom arg"})});ed.addButton("cart66",{title:"cart66.cart66_button_desc",cmd:"mcephproduct",image:url+"/img/cart66.gif"})},getInfo:function(){return{longname:"Cart66",author:"Andre Fredette",authorurl:"http://www.phpoet.com/",infourl:"http://www.phpoet.com/",version:"1.0"}}});tinymce.PluginManager.add("cart66",tinymce.plugins.cart66)})();