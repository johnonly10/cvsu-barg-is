@if($results->isEmpty())
    <p>No results found for "{{ $query }}"</p>
@else
    <ul>
        @foreach($results as $result)
            <li>{{ $result->name }}</li>
        @endforeach
    </ul>
@endif