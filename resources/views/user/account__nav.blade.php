<ul class="account-nav">
<ul class="account-nav">
    <li><a href="{{ route('user.index') }}" class="menu-link menu-link_us-s">Dashboard</a></li>
    <li><a href="{{ route('user.profile') }}" class="menu-link menu-link_us-s">Account</a></li>

    <!-- Orders Dropdown -->
    <li>
        <a href="{{ route('user.orders') }}" class="menu-link menu-link_us-s" >
            Orders
        </a>
    </li>
    <li><a href="{{ route('user.order.history') }}" class="menu-link menu-link_us-s">Order History</a></li>
    <li><a href="{{ route('user.reservation') }}" class="menu-link menu-link_us-s">Rentals</a></li>
    <li><a href="{{route('user.reservation.history')}}" class="menu-link menu-link_us-s">Rental History</a></li>
    <li>
        <form action="{{ route('logout') }}" method="post" id="logout-form">
            @csrf
            <a href="{{ route('logout') }}" class="menu-link menu-link_us-s" onclick="event.preventDefault();document.getElementById('logout-form').submit();">Logout</a>
        </form>
    </li>
</ul>