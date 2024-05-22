@extends('layouts.mail.app')
@section('content')
<tr>
    <td style="padding: 50px 45px 25px 45px; font-family: arial; font-size: 15px; color: #333; line-height: normal;"
            width="550">
           OTP Verification
    </td>
</tr>
    <tr>
        <td style="padding: 50px 45px 25px 45px; font-family: arial; font-size: 15px; color: #333; line-height: normal;"
            width="550">Hello <strong>{{$user->name}},</strong></td>
    </tr>
    <tr>
        <td style="padding: 0 45px 5px; font-family: arial; font-size: 14px; color: #333; line-height: normal;"
            width="550">We’ve received a request to verify email for your {{site_name}}  account.
        </td>
    </tr>
    <tr>
        <td  style="padding: 0 45px 5px; font-family: arial; font-size: 14px; color: #333; line-height: normal;"
            width="550">
        To verify your email, please use following OTP code: <b>{{$user->otp}}.</b>
        </td>
    </tr>
    <tr>
        <td  style="padding: 0 45px 5px; font-family: arial; font-size: 14px; color: #333; line-height: normal;"
            width="550">
        If you did not request verify email but received this email, please notify us at admin@gmail.com immediately.
        </td>
    </tr>
    
@endsection
