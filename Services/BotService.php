<?php 
namespace Modules\NsDemo\Services;

use App\Exceptions\NotAllowedException;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use Illuminate\Support\Arr;

class BotService
{
    private $request;
    private $telegram;

    public function __construct()
    {
        $this->telegram   =   new Telegram( 
            env( 'NS_BULKIMPORT_TELEGRAM_TOKEN' ), 
            env( 'NS_BULKIMPORT_TELEGRAM_USERNAME' )
        );
    }

    public function setWebhook()
    {
        try {
            $result     =   $this->telegram->setWebhook( route( 'ns-demo-telegram-webhook' ) );

            if ( $result->isOk() ) {
                return $result->getDescription();
            }

        } catch( Exception $exception ) {
            throw new NotAllowedException( $exception->getMessage() );
        }
    }

    public function unsetWebhook()
    {
        try {
            $result     =   $this->telegram->deleteWebhook();

            if ( $result->isOk() ) {
                return $result->getDescription();
            }

        } catch( Exception $exception ) {
            throw new NotAllowedException( $exception->getMessage() );
        }
    }

    public function handleWebHook( $data )
    {
        try {
            $this->telegram->handle();

            $this->handleRequest( $data );

        } catch( Exception $exception ) {
            throw new NotAllowedException( $exception->getMessage() );
        }
    }

    public function handleRequest( $data )
    {
        if ( isset( $data[ 'inline_query' ] ) ) {
            return $this->handleInlineQuery( $data );
        } else if ( isset( $data[ 'message' ] ) ) {
            return $this->handleMessageQuery( $data );
        }
    }

    private function handleMessageQuery( $data )
    {
        /**
         * ignore all bot message
         */
        if ( $data[ 'message' ][ 'from' ][ 'is_bot' ] === true ) {
            return;
        }

        if ( $data[ 'message' ][ 'from' ][ 'username' ] !== 'blair2004' ) {
            return Request::sendMessage([
                'chat_id'   =>  $data[ 'message' ][ 'chat' ][ 'id' ],
                'text'      =>  __( 'You\'re not my Lord 😒 !!!' )
            ]);
        }

        switch( $data[ 'message' ][ 'text' ] ) {
            case '/reset':
                return $this->resetCommand( $data );
            break;
        }

        return $this->unsupportedMessage( $data );
    }

    private function unsupportedMessage( $data )
    {
        return Request::sendMessage([
            'chat_id'   =>  $data[ 'message' ][ 'chat' ][ 'id' ],
            'text'      =>  __( 'Well im not able to understand that request 🤷!' )
        ]);
    }

    private function resetCommand( $data ) {
        try {
            Artisan::call( 'ns:reset' );
            Artisan::call( 'db:seed --class=DefaultSeeder' );
            Artisan::call( 'ns:bulkimport /storage/app/products.csv --email=contact@nexopos.com --config=/storage/app/import-config.json' );

            Request::sendMessage([
                'chat_id'   =>  $data[ 'message' ][ 'chat' ][ 'id' ],
                'text'      =>  __( 'The installation has been successfully reset.' )
            ]);

        } catch( Exception $exception ) {
            $string         =   Arr::random([
                __( 'Oups... looks like something goes wrong : %s' ),
                __( 'Hum... i\'ve faced an issue : %s' ),
                __( 'It didn\'t went as expected : %s' ),
                __( 'That\'s not what i was expecting : %s' ),
                __( 'Maybe we should try again ? I got this : %s' ),
                __( 'Ohh no... i\'ve meet an issue : %s' ),
                __( 'Well, it went bad : %s' ),
                __( 'Definitely not what i was expecting : %s' ),
                __( 'Does everything goes well on the server ? Here is what i got : %s' ),
                __( 'Something seems broken, see : %s' ),
                __( 'Yeah.. hum no... it didn\'t worked : %s' ),
                __( 'So bad... there is a problem : %s' ),
                __( 'I\'m sure that\'s not what is expected : %s' ),
            ]);

            $string     =   sprintf( $string, $exception->getMessage() );

            Request::sendMessage([
                'chat_id'   =>  $data[ 'message' ][ 'chat' ][ 'id' ],
                'text'      =>  $string
            ]);
        }
    }

    private function handleInlineQuery( $data )
    {
        /**
         * ignore all bot message
         */
        if ( $data[ 'inline_query' ][ 'from' ][ 'is_bot' ] === true ) {
            return;
        }
    }

    public function sendMessage( $config )
    {
        return Request::sendMessage( $config );
    }
}