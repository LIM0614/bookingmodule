@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <h2 class="mb-4">Admin: All Bookings</h2>

        {{-- 筛选表单 --}}
        <form id="filter-form" class="row g-3 mb-4">
            <div class="col-md-3">
                <input type="text" name="user_name" value="{{ request('user_name') }}" class="form-control"
                    placeholder="User Name">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="room_id" class="form-select">
                    <option value="">All Rooms</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                            {{ $room->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control"
                    placeholder="From">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" placeholder="To">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>

        {{-- 列表容器 --}}
        <div id="booking-list">
            @include('admin.bookings.partials.list', ['bookings' => $bookings])
        </div>

        {{-- 加载动画 --}}
        <div id="loading" class="text-center my-3" style="display:none;">
            Loading...
        </div>
    </div>

    {{-- 引入 Axios（或使用你项目已有的） --}}
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        (() => {
            let nextPageUrl = '{{ $bookings->nextPageUrl() }}';
            let loading = false;

            // 滚动到底部自动加载
            window.addEventListener('scroll', () => {
                if (!nextPageUrl || loading) return;
                if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 200) {
                    loadMore();
                }
            });

            // 筛选表单提交：清空列表并加载第一页
            document.getElementById('filter-form')
                .addEventListener('submit', function (e) {
                    e.preventDefault();
                    nextPageUrl = null;
                    document.getElementById('booking-list').innerHTML = '';
                    loadMore(true);
                });

            // 加载函数：第一次加载或滚动加载
            function loadMore(first = false) {
                loading = true;
                document.getElementById('loading').style.display = 'block';

                // 如果是第一次加载，跳到后台重新拿第一页
                const url = first
                    ? "{{ route('admin.bookings.index') }}"
                    : nextPageUrl;

                axios.get(url, {
                    params: new URLSearchParams(new FormData(
                        document.getElementById('filter-form')
                    ))
                })
                    .then(res => {
                        document.getElementById('booking-list').insertAdjacentHTML(
                            'beforeend',
                            res.data.html
                        );
                        nextPageUrl = res.data.next_page_url;
                    })
                    .finally(() => {
                        loading = false;
                        document.getElementById('loading').style.display = 'none';
                    });
            }
        })();
    </script>
@endsection