@php($data=[])
<?php
foreach ($store->schedules as $schedule)
{
    $data[$schedule->day][]=['id'=>$schedule->id,'start_time'=>$schedule->opening_time, 'end_time'=>$schedule->closing_time];
}
?>
<div class="schedule-item">
    <span class="btn">{{'lunes'}} :</span>
    <div class="schedult-date-content">
        @if(isset($data['1']) && count($data['1']))
            <span class="d-inline-flex align-items-center">
                @foreach ($data['1'] as $day)
                <span class="start--time">
                    <span class="clock--icon">
                        <i class="tio-time"></i>
                    </span>
                    <span class="info">
                        <span>{{'hora de apertura'}}</span>
                        {{date(config('timeformat'), strtotime($day['start_time']))}}
                    </span>
                </span>
                <span class="end--time">
                    <span class="clock--icon">
                        <i class="tio-time"></i>
                    </span>
                    <span class="info">
                        <span>{{'hora de cierre'}}</span>
                        {{date(config('timeformat'), strtotime($day['end_time']))}}
                    </span>
                </span>
                <span class="dismiss--date delete-schedule" data-url="{{route('admin.store.remove-schedule',['store_schedule'=>$day['id']])}}">
                    <i class="tio-clear-circle-outlined"></i>
                </span>
                @endforeach
            </span>
        @else
            <span class="btn btn-sm btn-outline-danger m-1 disabled">{{'día libre'}}</span>
        @endif
        <span class="btn add--primary" data-toggle="modal" data-target="#exampleModal" data-dayid="1" data-day="{{'lunes'}}"><i class="tio-add"></i></span>
    </div>
</div>

<div class="schedule-item">
    <span class="btn">{{'martes'}} :</span>
    <div class="schedult-date-content">
    @if(isset($data['2']) && count($data['2']))
            <span class="d-inline-flex align-items-center">
        @foreach ($data['2'] as $day)
                <span class="start--time">
                    <span class="clock--icon">
                        <i class="tio-time"></i>
                    </span>
                    <span class="info">
                        <span>{{'hora de apertura'}}</span>
                        {{date(config('timeformat'), strtotime($day['start_time']))}}
                    </span>
                </span>
                <span class="end--time">
                    <span class="clock--icon">
                        <i class="tio-time"></i>
                    </span>
                    <span class="info">
                        <span>{{'hora de cierre'}}</span>
                        {{date(config('timeformat'), strtotime($day['end_time']))}}
                    </span>
                </span>
                <span class="dismiss--date delete-schedule" data-url="{{route('admin.store.remove-schedule',['store_schedule'=>$day['id']])}}">
                    <i class="tio-clear-circle-outlined"></i>
                </span>
                @endforeach
            </span>
    @else
        <span class="btn btn-sm btn-outline-danger m-1 disabled">{{'día libre'}}</span>
    @endif
    <span class="btn add--primary" data-toggle="modal" data-target="#exampleModal" data-dayid="2" data-day="{{'martes'}}"><i class="tio-add"></i></span>
</div>
</div>
<div class="schedule-item">
        <span class="btn">{{'miércoles'}} :</span>
    <div class="schedult-date-content">
        @if(isset($data['3']) && count($data['3']))
            <span class="d-inline-flex align-items-center">
            @foreach ($data['3'] as $day)
                <span class="start--time">
                    <span class="clock--icon">
                        <i class="tio-time"></i>
                    </span>
                    <span class="info">
                        <span>{{'hora de apertura'}}</span>
                        {{date(config('timeformat'), strtotime($day['start_time']))}}
                    </span>
                </span>
                <span class="end--time">
                    <span class="clock--icon">
                        <i class="tio-time"></i>
                    </span>
                    <span class="info">
                        <span>{{'hora de cierre'}}</span>
                        {{date(config('timeformat'), strtotime($day['end_time']))}}
                    </span>
                </span>
                <span class="dismiss--date delete-schedule" data-url="{{route('admin.store.remove-schedule',['store_schedule'=>$day['id']])}}">
                    <i class="tio-clear-circle-outlined"></i>
                </span>
                @endforeach
            </span>
        @else
            <span class="btn btn-sm btn-outline-danger m-1 disabled">{{'día libre'}}</span>
        @endif
        <span class="btn add--primary" data-toggle="modal" data-target="#exampleModal" data-dayid="3" data-day="{{'miércoles'}}"><i class="tio-add"></i></span>
</div>
</div>

<div class="schedule-item">
        <span class="btn">{{'jueves'}} :</span>
    <div class="schedult-date-content">
        @if(isset($data['4']) && count($data['4']))
            <span class="d-inline-flex align-items-center">
            @foreach ($data['4'] as $day)
                <span class="start--time">
                    <span class="clock--icon">
                        <i class="tio-time"></i>
                    </span>
                    <span class="info">
                        <span>{{'hora de apertura'}}</span>
                        {{date(config('timeformat'), strtotime($day['start_time']))}}
                    </span>
                </span>
                <span class="end--time">
                    <span class="clock--icon">
                        <i class="tio-time"></i>
                    </span>
                    <span class="info">
                        <span>{{'hora de cierre'}}</span>
                        {{date(config('timeformat'), strtotime($day['end_time']))}}
                    </span>
                </span>
                <span class="dismiss--date delete-schedule" data-url="{{route('admin.store.remove-schedule',['store_schedule'=>$day['id']])}}">
                    <i class="tio-clear-circle-outlined"></i>
                </span>
                @endforeach
            </span>
        @else
            <span class="btn btn-sm btn-outline-danger m-1 disabled">{{'día libre'}}</span>
        @endif
        <span class="btn add--primary" data-toggle="modal" data-target="#exampleModal" data-dayid="4" data-day="{{'jueves'}}"><i class="tio-add"></i></span>
</div>
</div>

<div class="schedule-item">
        <span class="btn">{{'viernes'}} :</span>
    <div class="schedult-date-content">
        @if(isset($data['5']) && count($data['5']))
            <span class="d-inline-flex align-items-center">
            @foreach ($data['5'] as $day)
                <span class="start--time">
                    <span class="clock--icon">
                        <i class="tio-time"></i>
                    </span>
                    <span class="info">
                        <span>{{'hora de apertura'}}</span>
                        {{date(config('timeformat'), strtotime($day['start_time']))}}
                    </span>
                </span>
                <span class="end--time">
                    <span class="clock--icon">
                        <i class="tio-time"></i>
                    </span>
                    <span class="info">
                        <span>{{'hora de cierre'}}</span>
                        {{date(config('timeformat'), strtotime($day['end_time']))}}
                    </span>
                </span>
                <span class="dismiss--date delete-schedule" data-url="{{route('admin.store.remove-schedule',['store_schedule'=>$day['id']])}}">
                    <i class="tio-clear-circle-outlined"></i>
                </span>
                @endforeach
            </span>
        @else
            <span class="btn btn-sm btn-outline-danger m-1 disabled">{{'día libre'}}</span>
        @endif
        <span class="btn add--primary" data-toggle="modal" data-target="#exampleModal" data-dayid="5" data-day="{{'viernes'}}"><i class="tio-add"></i></span>
</div>
</div>

<div class="schedule-item">
        <span class="btn">{{'sábado'}} :</span>
    <div class="schedult-date-content">
        @if(isset($data['6']) && count($data['6']))
            <span class="d-inline-flex align-items-center">
            @foreach ($data['6'] as $day)
                <span class="start--time">
                    <span class="clock--icon">
                        <i class="tio-time"></i>
                    </span>
                    <span class="info">
                        <span>{{'hora de apertura'}}</span>
                        {{date(config('timeformat'), strtotime($day['start_time']))}}
                    </span>
                </span>
                <span class="end--time">
                    <span class="clock--icon">
                        <i class="tio-time"></i>
                    </span>
                    <span class="info">
                        <span>{{'hora de cierre'}}</span>
                        {{date(config('timeformat'), strtotime($day['end_time']))}}
                    </span>
                </span>
                <span class="dismiss--date delete-schedule" data-url="{{route('admin.store.remove-schedule',['store_schedule'=>$day['id']])}}">
                    <i class="tio-clear-circle-outlined"></i>
                </span>
                @endforeach
            </span>
        @else
            <span class="btn btn-sm btn-outline-danger m-1 disabled">{{'día libre'}}</span>
        @endif
        <span class="btn add--primary" data-toggle="modal" data-target="#exampleModal" data-dayid="6" data-day="{{'sábado'}}"><i class="tio-add"></i></span>
    </div>
</div>

<div class="schedule-item">
    <span class="btn">{{'domingo'}} :</span>
    <div class="schedult-date-content">
        @if(isset($data['0']) && count($data['0']))
            <span class="d-inline-flex align-items-center">
            @foreach ($data['0'] as $day)
                <span class="start--time">
                    <span class="clock--icon">
                        <i class="tio-time"></i>
                    </span>
                    <span class="info">
                        <span>{{'hora de apertura'}}</span>
                        {{date(config('timeformat'), strtotime($day['start_time']))}}
                    </span>
                </span>
                <span class="end--time">
                    <span class="clock--icon">
                        <i class="tio-time"></i>
                    </span>
                    <span class="info">
                        <span>{{'hora de cierre'}}</span>
                        {{date(config('timeformat'), strtotime($day['end_time']))}}
                    </span>
                </span>
                <span class="dismiss--date delete-schedule" data-url="{{route('admin.store.remove-schedule',['store_schedule'=>$day['id']])}}">
                    <i class="tio-clear-circle-outlined"></i>
                </span>
                @endforeach

            </span>
        @else
            <span class="btn btn-sm btn-outline-danger m-1 disabled">{{'día libre'}}</span>
        @endif
        <span class="btn add--primary" data-toggle="modal" data-target="#exampleModal" data-dayid="0" data-day="{{'domingo'}}"><i class="tio-add"></i></span>
    </div>
</div>
