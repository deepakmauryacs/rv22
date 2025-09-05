@php
$msgCount=getMessageCount();
$panel='';
if(auth()->user()->user_type==1)
{
$panel='buyer';
}elseif(auth()->user()->user_type==2)
{
$panel='vendor';
}elseif(auth()->user()->user_type==3)
{
$panel='super-admin';
}

$url=url($panel.'/message');
@endphp

@if (Auth::user()->user_type == '3' || Auth::user()->user_type == '2')
<li class="@if (session()->has('message_type'))
    {{ session()->get('message_type')=='buyer'?'active-menu':'' }}
    @endif">
    <a href="{{ $url }}?t=buyer">Buyer (<span>{{
            collect($msgCount)->firstWhere('user_type', '1')?->inbox_unread_count ?? 0 }}</span>)
    </a>
</li>
@endif

@if (Auth::user()->user_type == '3' || Auth::user()->user_type == '1')
<li class="@if (session()->has('message_type'))
    {{ session()->get('message_type')=='vendor'?'active-menu':'' }}
    @endif">
    <a href="{{ $url }}?t=vendor">Vendor (<span>{{
            collect($msgCount)->firstWhere('user_type', '2')?->inbox_unread_count ?? 0 }}</span>)
    </a>
</li>
@endif

@if (Auth::user()->user_type == '2' || Auth::user()->user_type == '1')
<li class="@if (session()->has('message_type'))
    {{ session()->get('message_type')=='raprocure'?'active-menu':'' }}
    @endif">
    <a href="{{ $url }}?t=raprocure">RaProcure (<span>{{
            collect($msgCount)->firstWhere('user_type', '3')?->inbox_unread_count ?? 0 }}</span>)
    </a>
</li>
@endif