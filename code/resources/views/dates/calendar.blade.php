<script>
    var dates_events = [
        @foreach(App\Aggregate::easyFilter(null, null, null, ['open', 'closed']) as $a)
            @if($a->shipping)
                {
                    title: '{!! join(', ', $a->orders->reduce(function($carry, $item) { $carry[] = addslashes($item->supplier->name); return $carry; }, [])) !!}',
                    start: '{{ $a->shipping }}',
                    className: 'calendar-shipping-{{ $a->status }}',
                    url: '{{ $a->getBookingURL() }}'
                },
            @endif
        @endforeach

        @foreach(App\Date::all() as $d)
            @foreach($d->dates as $dat)
                {
                    title: '{{ $d->calendar_string }}',
                    start: '{{ $dat }}',
                    className: 'calendar-date-{{ $d->type }}'
                },
            @endforeach
        @endforeach
    ];
</script>

<div id="dates-calendar">
    <div class="row">
        <div class="col-md-3">
            <span class="fc-event calendar-shipping-open">{{ _i('Ordini Aperti') }}</span>
            <span class="fc-event calendar-shipping-closed">{{ _i('Ordini Chiusi') }}</span>
            @if(App\Date::count())
                <span class="fc-event calendar-date-confirmed">{{ _i('Date Confermate') }}</span>
                <span class="fc-event calendar-date-temp">{{ _i('Date Temporanee') }}</span>
                <span class="fc-event calendar-date-internal">{{ _i('Appuntamenti') }}</span>
            @endif
        </div>
    </div>
</div>
