<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/semantic-ui/2.2.4/semantic.min.css">
        <script   src="https://code.jquery.com/jquery-3.1.1.min.js"   integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="   crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/semantic-ui/2.2.4/semantic.min.js"></script>
        <title>Laravel</title>

        <!-- Styles -->
        <style>

            body > .ui.container {
                margin-top: 3em;
            }
            iframe {
                border: none;
                width: calc(100% + 2em);
                margin: 0em -1em;
                /*height: 300px;*/
            }
            iframe html {
                overflow: hidden;
            }
            iframe body {
                padding: 0em;
            }

            .ui.container > h1 {
                font-size: 3em;
                text-align: center;
                font-weight: normal;
            }
            .ui.container > h2.dividing.header {
                font-size: 2em;
                font-weight: normal;
                margin: 4em 0em 3em;
            }


            .ui.table {
                table-layout: fixed;
            }

        </style>
    </head>
    <body>
    <div class="ui top menu">
        <div class="main ui container">
        <div class="right menu">
            @if ($user)
                <form class="right menu" method="POST" action="{{url('give')}}">
                    {{ csrf_field() }}
                    <input type="hidden" name="userID" value="{{$user->id}}">
                    <div class="item">
                        <div class="ui right labeled left icon input">
                            <i class="money icon"></i>
                            <input type="text" name="amount" placeholder="Enter amount" value="{{$user->getBalance()}}">
                            <button type="submit" class="ui tag green label">
                                Set Balance
                            </button>
                        </div>
                    </div>
                </form>
            @endif
            <form class="right menu" method="POST">
                @if (!$user)
                    {{ csrf_field() }}
                    <div class="item">
                        <div class="ui right labeled left icon input">
                            <i class="mail icon"></i>
                            <input type="text" name="login_name" placeholder="Enter username" value="{{array_get($input, 'login_name')}}">
                            <a class="ui tag label">
                                Email
                            </a>
                        </div>
                    </div>
                    <div class="item">
                        <div class="ui right labeled left icon input">
                            <i class="lock icon"></i>
                            <input type="password" name="login_password" placeholder="Enter password" value="{{array_get($input, 'login_password')}}">
                            <a class="ui tag label">
                                Pass
                            </a>
                        </div>
                    </div>
                    <div class="item">
                        <button type="submit" class="tiny ui green right labeled icon submit button">
                            <i class="right plug icon"></i>
                            Login
                        </button>
                    </div>
                @else
                    <div class="item">
                        <i class="user icon"></i>
                        {{$user->login}}
                    </div>
                    <div class="item">
                        <i class="money icon"></i>
                        <b>{{$user->getBalance()}} : {{$user->getCurrency()}}</b>
                    </div>
                    <div class="item">
                        <a href="{{url('logout')}}" class="tiny ui red right labeled icon button">
                            <i class="right power icon"></i>
                            Logout
                        </a>
                    </div>
                @endif
            </form>
        </div>
            </div>
    </div>
    <div class="main ui container">
        <form class="ui form">
            <div class="ui three column stackable grid">
                <div class="seven wide column">
                    <div class="ui fluid labeled input">
                        <a class="ui blue label">
                            <i class="plug icon"></i>
                            Game Provider
                        </a>
                        <select name="provider" class="ui icon fluid dropdown" id="select">
                            <option value="allproviders" @if (!array_get($input, 'provider') || array_get($input, 'provider') == 'allproviders') selected @endif>All</option>
                            @foreach ($gameProviders as $provider)
                                <option value="{{$provider['name']}}" @if (array_get($input, 'provider') == $provider['name']) selected @endif>{{$provider['full_name']}}</option>
                            @endforeach
                        </select>
                        <div class="ui corner label inverted blue">
                            <i class="filter icon"></i>
                        </div>
                    </div>
                </div>
                <div class="seven wide column">
                    <div class="ui fluid labeled input">
                        <a class="ui blue label">
                            <i class="soccer icon"></i>
                            Game Type
                        </a>
                        <select name="gameType" class="ui icon fluid dropdown" id="select">
                            <option value="alltypes" @if (!array_get($input, 'gameType') || array_get($input, 'gameType') == 'alltypes') selected @endif>All</option>
                            @foreach ($gameTypes as $type)
                                <option value="{{$type['game_type']}}" @if (array_get($input, 'gameType') == $type['game_type']) selected @endif>{{$type['game_type_tr']}}</option>
                            @endforeach
                        </select>
                        <div class="ui corner label inverted blue">
                            <i class="filter icon"></i>
                        </div>
                    </div>
                </div>
                <div class="two wide column">
                    <button type="submit" class="ui green fluid submit button">
                        Filter
                    </button>
                </div>
            </div>
        </form>
        <table class="ui blue selectable celled striped table">
            <thead>
                <tr>
                    <th class="five wide">Title</th>
                    <th>Game type</th>
                    <th>Game provider</th>
                    <th class="one wide">On</th>
                    <th class="one wide">Mobile</th>
                    <th class="center aligned"><i class="green game icon"></i> Actions</th>
                </tr>
            </thead>
            <tbody>
                @if (!$games)
                    <tr class="warning">
                        <td colspan="6">There is no games with given parameters</td>
                    </tr>
                @else
                    @foreach ($games as $game)
                        <tr>
                            <td>{{$game['title']}}</td>
                            <td>{{\Stringy\StaticStringy::humanize($game['game_type'])}}</td>
                            <td>{{$game['full_name']}}</td>
                            <td class="center aligned">
                                @if ($game['enable'])
                                    <i class="green checkmark icon"></i>
                                @else
                                    <i class="red remove icon"></i>
                                @endif
                            </td>
                            <td class="center aligned">
                                @if ($game['mobile'])
                                    <i class="green checkmark icon"></i>
                                @else
                                    <i class="red remove icon"></i>
                                @endif
                            </td>
                            <td class="center aligned">
                                @if (!$game['enable'])
                                    &mdash;
                                @else
                                    <?php $query = http_build_query([
                                        'gameType' => $game['game_type'],
                                        'gameUrl'  => $game['url'],
                                        'lang'     => $game['lang'] ? : 'ru',
                                        'isMobile' => $game['mobile'] ? : false,
                                        'isDemo'   => false
                                    ]); ?>
                                    <a href="{{$baseUrl}}/internal/games/game?{{$query}}" class="mini ui right labeled green icon button rungame">
                                        <i class="right arrow icon"></i>
                                        Run
                                    </a>
                                    @if ($game['demo'])
                                        <?php $query = http_build_query([
                                                'gameType' => $game['game_type'],
                                                'gameUrl'  => $game['url'],
                                                'lang'     => $game['lang'] ? : 'ru',
                                                'isMobile' => $game['mobile'] ? : false,
                                                'isDemo'   => true
                                        ]); ?>
                                        <a href="{{$baseUrl}}/internal/games/game?{{$query}}" class="mini ui blue button rungame">
                                            Demo
                                        </a>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
    <div class="ui basic modal">
        <i class="close icon"></i>
        <div class="header">
            Game Name
        </div>
        <div class="image content" id="iframe">
            <div class="image">
                <i class="warning sign icon"></i>
            </div>
            <div class="description">
                <p>Your inbox is getting full, would you like us to enable automatic archiving of old messages?</p>
            </div>
        </div>
    </div>
    </body>
</html>

<script>

    window.template = '';

    $(document).ready(function() {
        window.template = $('.ui.basic.modal #iframe').html();
    });


    $('.rungame').on('click', function(e){
        e.preventDefault();

        var modal = $('.ui.basic.modal');

        var iframe = $('.ui.basic.modal #iframe');

        var header = $('.ui.basic.modal .header');

        var url = $(this).attr('href');

        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'JSON',
            success: function (data) {
                if(data.status == true) {
                    console.log(data);
                    header.html(data.game_name + " " + data.provider_name);

                    var template = '<iframe src="' + data.url + '" height="650"></iframe>';

                    var ifrm = document.createElement("iframe");
                    ifrm.setAttribute("src", data.url);
                    ifrm.style.height = "650px";

                    Object.defineProperty(ifrm, "referrer", {get : function(){ return "https://favbet.dev/"; }});

                    iframe.html(ifrm);

                    //iframe.html(template);


                    modal.modal('show');
                } else {
                    header.html(window.template);
                    header.html('Error');
                    $('.ui.basic.modal .content .description').html(data.message);

                    modal.modal('show');
                }
            },
            error: function(data){
                console.log(data);
            }
        });
    });
</script>
