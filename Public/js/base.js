function dialog2(error){
	var dialog2 = '<div class="weui_dialog_alert" id="dialog2" style="z-index:9999999;"><div class="weui_mask"></div><div class="weui_dialog"><div class="weui_dialog_hd"><strong class="weui_dialog_title">提醒</strong></div><div class="weui_dialog_bd">'+error+'</div><div class="weui_dialog_ft"><a href="javascript:;" class="weui_btn_dialog primary" style="color:#ff6666" onclick="closedialog2()">确定</a></div></div></div>';
	$(document.body).append(dialog2);
}
function closedialog2(){
	$("#dialog2").remove();
}

function loading(info){
	var load = '<div id="loadingToast" class="weui_loading_toast" style="z-index:9999999;"><div class="weui_mask_transparent"></div><div class="weui_toast"><div class="weui_loading"><div class="weui_loading_leaf weui_loading_leaf_0"></div><div class="weui_loading_leaf weui_loading_leaf_1"></div><div class="weui_loading_leaf weui_loading_leaf_2"></div><div class="weui_loading_leaf weui_loading_leaf_3"></div><div class="weui_loading_leaf weui_loading_leaf_4"></div><div class="weui_loading_leaf weui_loading_leaf_5"></div><div class="weui_loading_leaf weui_loading_leaf_6"></div><div class="weui_loading_leaf weui_loading_leaf_7"></div><div class="weui_loading_leaf weui_loading_leaf_8"></div><div class="weui_loading_leaf weui_loading_leaf_9"></div><div class="weui_loading_leaf weui_loading_leaf_10"></div><div class="weui_loading_leaf weui_loading_leaf_11"></div></div><p class="weui_toast_content">'+info+'</p></div></div>';
	$(document.body).append(load);
}
function loadingclose(){
	$('#loadingToast').remove();
}

function groupSheet(wxid,user_id){
	var actionSheet = '<div id="actionSheet_wrap"><div class="weui_mask_transition weui_fade_toggle" id="mask" onclick="groupSheetclose()" style="display: block;"></div><div class="weui_actionsheet weui_actionsheet_toggle" id="weui_actionsheet"><div class="weui_actionsheet_menu"><div class="weui_actionsheet_cell">ta的微信号：'+wxid+'</div><div class="weui_actionsheet_cell" onclick="chat('+user_id+')">和ta在线聊天</div></div><div class="weui_actionsheet_action"><div class="weui_actionsheet_cell" id="actionsheet_cancel" onclick="groupSheetclose()">取消</div></div></div></div>';
	$(document.body).append(actionSheet);
}
function groupSheetclose(){
	$('#actionSheet_wrap').remove();
}

function centerset(){
	var actionSheet = '<div id="centerset" style="z-index:99999999"><div class="weui_mask_transition weui_fade_toggle" id="mask" onclick="centersetclose()" style="display: block;"></div><div class="weui_actionsheet weui_actionsheet_toggle" id="weui_actionsheet"><div class="weui_actionsheet_menu"><div class="weui_actionsheet_cell" onclick="updtuserinfo()">更新头像昵称信息</div><div class="weui_actionsheet_cell" onclick="resetqr()">重置个人二维码</div></div><div class="weui_actionsheet_action"><div class="weui_actionsheet_cell" id="actionsheet_cancel" onclick="centersetclose()">取消</div></div></div></div>';
	$(document.body).append(actionSheet);
}
function centersetclose(){
	$('#centerset').remove();
}