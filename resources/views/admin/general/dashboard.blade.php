@extends('layouts.master')
<style>
    .rounded-img1{
        width: 100%;
        height: 40px;
        background-color:#6FCA7F;
    }
     .item-caption {
         font-size: 16px;
         color: black;
         text-transform: uppercase;
         font-weight: bold;
     }
    .item-digit {
        font-size: 36px;
        color: #6FCA7F;
        font-weight: bold;
    }
</style>
@section('content')

<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-md-2 text-center">
            <div class="item-caption">Total Members</div>
            <div class="item-digit">{{$members_count}}</div>
        </div>
        <div class="col-md-2 text-center">
            <div class="item-caption">Members Today</div>
            <div class="item-digit">{{$members_today_count}}</div>
        </div>
        <div class="col-md-2 text-center">
            <div class="item-caption">Total Recipes</div>
            <div class="item-digit">{{$recipes_count}}</div>
        </div>
        <div class="col-md-2 text-center">
            <div class="item-caption">Billboards Ads</div>
            <div class="item-digit">{{$billboards_count}}</div>
        </div>
        <div class="col-md-2 text-center">
            <div class="item-caption">Horizontal Feeds</div>
            <div class="item-digit">{{$feeds_count}}</div>
        </div>
        <div class="col-md-2 text-center">
            <div class="item-caption">Sponsored Videos</div>
            <div class="item-digit">{{$sponsored_videos_count}}</div>
        </div>
    </div>
</div>

<hr>

<div class="row wd-sl-rowmain">
    <div class="col-xl-4">
        <div class="card overflow-hidden">
            <div class="bg-success bg-soft">
                <div class="row">
                    <div class="col-7">
                        <div class="text-primary p-3">
                            <h5 class="text-primary">Welcome Back !</h5>
                        </div>
                    </div>
                    <div class="col-5 align-self-end"> <img src="{{ URL::asset('/assets/images/profile-img.png') }}" alt="" class="img-fluid"> </div>
                </div>
            </div>
            <div class="card-body pt-0">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="avatar-md profile-user-wid mb-4"> <img src="{{ isset(Auth::user()->profile_image) ? asset(Auth::user()->profile_image) : asset('/assets/images/users/avatar-1.jpg') }}" alt="" class="rounded-img img-thumbnail rounded-circle"> </div>
                        <h5 class="font-size-15 text-truncate">{{ucfirst(@Auth::user()->name)}}</h5>
                    </div>
                    <div class="col-sm-8">
                        <div class="pt-4">
                            <div class="row">
                            </div>
                            <div class="mt-4"> <a href="{{route('admin.profile')}}" class="btn btn-success waves-effect waves-light btn-sm">View Profile <i class="mdi mdi-arrow-right ms-1"></i></a> </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card wd-sl-dashcard">
            <div class="card-body">
                <h5 class="mb-4">Total User </h5>
                <h1>{{$user_count}}</h1>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="row wd-sl-otherrow">
            <div class="col-md-4">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium">Cuisines</p>
                                <h4 class="mb-0">{{$cuisines_count}}</h4>
                            </div>
                            <div class="flex-shrink-0 align-self-center">
                                <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                                    <span class="avatar-title" >
                                    <i class="fas fa-utensils font-size-24"></i>
                                        <!-- <i class="bx bx-copy-alt font-size-24"></i> -->
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium">Food And Drink</p>
                                <h4 class="mb-0">{{$food_and_drink_count}}</h4>
                            </div>
                            <div class="flex-shrink-0 align-self-center ">
                                <div class="avatar-sm rounded-circle bg-primary mini-stat-icon">
                                    <span class="avatar-title rounded-circle ">
                                    <img src="{{asset('/assets/images/Food-Drink.png') }}" alt="" class="rounded-img1  rounded-circle">
                                    <!-- <i class="fas fa-pizza-slice font-size-24"></i>
                                        <i class="bx bx-archive-in font-size-24"></i> -->
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium">Diet</p>
                                <h4 class="mb-0">{{$diet_count}}</h4>
                            </div>
                            <div class="flex-shrink-0 align-self-center">
                                <div class="avatar-sm rounded-circle bg-primary mini-stat-icon">
                                    <span class="avatar-title rounded-circle ">
                                    <i class="fas fa-burn font-size-24"></i>
                                        <!-- <i class="bx bx-purchase-tag-alt font-size-24"></i> -->
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card h-100">
            <div class="card-body">
                <div id="container" style="width: 100%;">
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('script')
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/data.js"></script>

<script>
    Highcharts.getJSON(
        '{{route("admin.totalusers")}}',
        function(data) {
            Highcharts.setOptions({
               colors: ['#6FCA7F']
             });
            Highcharts.chart('container', {
                chart: {
                    zoomType: 'x'
                },
                title: {
                    text: 'Registered Users'
                },
                subtitle: {
                    text: document.ontouchstart === undefined ?
                        'Click and drag in the plot area to zoom in' : 'Pinch the chart to zoom in'
                },
                xAxis: {
                    type: 'datetime'
                },
                yAxis: {
                    title: {
                        text: 'Registered Users'
                    }
                },
                legend: {
                    enabled: false
                },
                plotOptions: {
                    area: {
                        fillColor: {
                            linearGradient: {
                                x1: 0,
                                y1: 0,
                                x2: 0,
                                y2: 1
                            },
                            stops: [
                                [0, Highcharts.getOptions().colors[0]],
                                [1, Highcharts.color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                            ]
                        },
                        marker: {
                            radius: 2
                        },
                        lineWidth: 1,
                        states: {
                            hover: {
                                lineWidth: 1
                            }
                        },
                        threshold: null
                    }
                },

                series: [{
                    type: 'area',
                    name: 'USERS',
                    data: data
                }]
            });
        }
    );
</script>
@endsection
