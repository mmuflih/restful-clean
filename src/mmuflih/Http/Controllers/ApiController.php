<?php

/**
 * Created by Muhammad Muflih Kholidin
 * https://github.com/mmuflih
 * muflic.24@gmail.com
 **/

namespace MMuflih\Http\Controllers;

use MMuflih\Context\Handler;
use MMuflih\Context\PagedList;
use MMuflih\Context\Reader;
use MMuflih\Jobs\TelegramJob;
use MMuflih\Jobs\WriteLog;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Laravel\Lumen\Routing\Controller;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Telegram\Bot\Api;

class ApiController extends Controller
{
    public function responseData($data, $code = 200)
    {
        $struct = [
            'data' => $data,
            'message' => 'success',
            'code' => $code
        ];
        return new JsonResponse($struct, $code);
    }

    public function responseReader(Reader $reader, Request $request = null, $validator = null)
    {
        try {
            if ($request && $validator) {
                $this->validate($request, $validator);
            }
            $data = $reader->read();
            return $this->responseData($data);
        } catch (\Exception $e) {
            return $this->responseException($e);
        }
    }

    public function responseHandler(Handler $handler, Request $request = null, $validator = null, $messages = [])
    {
        $this->logRequest($request);
        try {
            if ($request && $validator) {
                $this->validate($request, $validator, $messages);
            }
            $data = $handler->handle();
            try {
                if (is_object($data)) {
                    $data->queue();
                }
            } catch (\Exception $e) {
            }
            return $this->responseData($data);
        } catch (\Exception $e) {
            return $this->responseException($e);
        }
    }

    /**
     * @param Reader $reader 
     * @return JsonResponse 
     */
    public function responseHasPaginate(Reader $reader)
    {
        try {
            /** @var LengthAwarePaginator $data */
            /** @var HasPaginate $reader */
            $data = $reader->paginate();
            return $this->responsePaginate($data);
        } catch (\Exception $e) {
            return $this->responseException($e);
        }
    }

    public function responseHasPaginateReader(Reader $reader)
    {
        try {
            $data = $reader->read();
            return $this->responsePagedList($data);
        } catch (\Exception $e) {
            return $this->responseException($e);
        }
    }

    public function responsePagedList(PagedList $data, $code = 200)
    {
        $currPage = $data->getCurrentPage();
        $totalPages = (int)ceil($data->getTotalRow() / $data->getItemsPerPage());
        $struct = [
            'data' => $data->getData(),
            'message' => 'success',
            'code' => $code,
            "paginate" => [
                "current_page" => $currPage,
                "next_page" => $currPage == $totalPages ? null : $currPage + 1,
                "per_page" => $data->getItemsPerPage(),
                "prev_page" => $currPage == 1 ? null : $currPage + 1,
                "total_entries" => $data->getTotalRow(),
                "total_pages" => $totalPages
            ]
        ];
        return new JsonResponse($struct, $code);
    }

    public function responsePaginate(LengthAwarePaginator $data, $code = 200)
    {
        $currPage = $data->currentPage();
        $total = $data->total();
        $totalPages = (int)ceil($total / $data->perPage());
        $struct = [
            'data' => $data->items(),
            'message' => 'success',
            'code' => $code,
            "paginate" => [
                "current_page" => $currPage,
                "next_page" => $currPage == $totalPages ? null : $currPage + 1,
                "per_page" => $data->perPage(),
                "prev_page" => $currPage == 1 ? null : $currPage + 1,
                "total_entries" => $total,
                "total_pages" => $totalPages
            ]
        ];
        return new JsonResponse($struct, $code);
    }

    public function responseDataAndMessage($data, $message, $code = 200)
    {
        $struct = [
            'data' => $data,
            'message' => $message,
            'code' => $code
        ];
        return new JsonResponse($struct, $code);
    }

    public function responseException(\Exception $e)
    {
        $code = $e->getCode();
        $validCode = $this->getHttpCode($code);
        $devMessage = $e->getMessage() . '. On file ' . $e->getFile() . ' line ' . $e->getLine();
        $userMessage = $e->getMessage();
        $errorFormat = [
            'status' => $code,
            'status_desc' => $validCode['desc'],
            'developer_message' => $devMessage,
            'user_message' => $userMessage,
            'error_code' => $validCode['code'],
            'more_info' => 'Contact Administrator'
        ];
        if (method_exists($e, 'errors')) {
            /** @var ValidatorException $e */
            $validHttpCode = 422;
            unset($errorFormat['developer_message']);
            $errorFormat['errors'] = $e->errors();
            $errorFormat['status'] = $validHttpCode;
            $errorFormat['error_code'] = $validHttpCode;
        }
        try {
            $telegram = new Api(env("TELEGRAM_BOT"));
            $response = $telegram->sendMessage([
                'chat_id' => '@' . env("TELEGRAM_CHANNEL"),
                'text' => "```" . json_encode($errorFormat, JSON_PRETTY_PRINT) . "```",
                'parse_mode' => 'Markdown'
            ]);

            $response->getMessageId();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return new JsonResponse($errorFormat, $validCode['code']);
    }

    private static $validHttpCodeList = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];

    private static function getHttpCode($code)
    {
        try {
            return [
                'code' => $code,
                'desc' => self::$validHttpCodeList[$code],
            ];
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'desc' => $e->getMessage()
            ];
        }
    }

    private function logRequest($request)
    {
        try {
            if ($request) {
                $url = $this->getUrl($request);
                $reqFormat = array_merge([
                    'url' => $url,
                ], $request->all());
                $this->sendTelegram($reqFormat);
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * @param Array $reqFormat 
     * @return void 
     * @throws BindingResolutionException 
     * @throws InvalidArgumentException 
     * @throws BadRequestException 
     * @throws SuspiciousOperationException 
     */
    private function sendTelegram($reqFormat)
    {
        try {
            $tj = new TelegramJob("```" . json_encode($reqFormat, JSON_PRETTY_PRINT) . "```");
            app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($tj);
        } catch (\Exception $e) {
        }
    }

    private function getUrl(Request $request)
    {
        try {
            return $request->getHttpHost() .
                $request->getRequestUri();
        } catch (\Exception $e) {
            return '';
        }
        return '';
    }
}
