@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Reset Password</div>
                    <div class="card-body">
                        <h1> A Senha para o Email {{$email}} foi alterada com sucesso</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection