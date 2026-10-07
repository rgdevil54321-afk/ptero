@extends('templates/wrapper', [
    'css' => ['body' => 'bg-hyper-background']
])

@section('container')
    <div id="app"></div>
@endsection

@section('scripts')
    @parent
    @if(isset($ssoData))
        <script>
            window.SsoPendingLink = {!! $ssoData !!};
        </script>
    @endif
    @if(session('sso_pending'))
        <script>
            window.SsoPendingLinkStatus = true;
        </script>
    @endif
@endsection
