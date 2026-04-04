@php
$adminTheme = Auth::guard('marble')->user()?->theme ?? 'xp';
if ($adminTheme === '98') {
    $win98map = [
        'add'                   => 'plus.png',
        'application_form'      => 'document-0.png',
        'application_side_expand' => 'expand_hierarchial_array-0.png',
        'application_side_tree' => 'tree-0.png',
        'application_xp'        => 'start_menu_xp-0.png',
        'attachment'            => 'nail-0.png',
        'bell'                  => 'msg_information-0.png',
        'bin'                   => 'recycle_bin_full-0.png',
        'bin_empty'             => 'recycle_bin_empty-0.png',
        'box'                   => 'package-0.png',
        'brick'                 => 'tools_gear-0.png',
        'cancel'                => 'no-0.png',
        'chart_bar'             => 'bar_graph-0.png',
        'clock'                 => 'clock-0.png',
        'cog'                   => 'gears-0.png',
        'connect'               => 'conn_pcs_on_on.png',
        'disconnect'            => 'conn_pcs_off_off.png',
        'disk'                  => 'floppy_drive_3_5-0.png',
        'error'                 => 'msg_error-0.png',
        'folder'                => 'directory_closed-0.png',
        'folder_add'            => 'directory_closed-0.png',
        'folder_page'           => 'directory_open_file_mydocs-0.png',
        'group'                 => 'users-0.png',
        'house'                 => 'homepage-0.png',
        'image'                 => 'wia_img_color-0.png',
        'key'                   => 'key_win-0.png',
        'link'                  => 'url1-0.png',
        'lock'                  => 'key_padlock-0.png',
        'lock_key'              => 'key_padlock-0.png',
        'monitor'               => 'monitor_blue_grad-0.png',
        'page_copy'             => 'diskettes_copy-0.png',
        'page_white'            => 'document-0.png',
        'page_white_copy'       => 'diskettes_copy-0.png',
        'page_white_paste'      => 'document-0.png',
        'pencil'                => 'rename-0.png',
        'pictures'              => 'wia_img_color-0.png',
        'plus'                  => 'plus.png',
        'report'                => 'notepad_file-0.png',
        'server'                => 'server_gear-0.png',
        'status_online'         => 'conn_cloud_ok.png',
        'target'                => 'magnifying_glass-0.png',
        'tick'                  => 'check-0.png',
        'time'                  => 'clock-0.png',
        'trash'                 => 'recycle_bin_full-0.png',
        'user'                  => 'user_card.png',
        'user_edit'             => 'computer_user_pencil-0.png',
        'world'                 => 'world-0.png',
        'wrench'                => 'tools_gear-0.png',
        'zoom'                  => 'magnifying_glass-0.png',
    ];
    if (isset($win98map[$name])) {
        $iconSrc = asset('vendor/marble/assets/images/win98icons/' . $win98map[$name]);
    } else {
        $iconSrc = asset('vendor/marble/assets/images/famicons/' . $name . '.svg');
    }
} else {
    $iconSrc = asset('vendor/marble/assets/images/famicons/' . $name . '.svg');
}
@endphp
<img src="{{ $iconSrc }}" width="16" height="16" alt="" class="marble-famicon">
