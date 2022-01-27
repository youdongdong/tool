<?php

namespace tool\common;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Excel
{
    /**允许的扩展名 */
    const ALLOW_EXT = ['Xlsx', 'xlsx', 'Xls', 'xls', 'Html', 'html'];
    /**数据 */
    protected $data;
    /**文件扩展名 */
    protected $ext = 'Xlsx';

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * 生成文件.
     *
     * @param string $path 生成文件保存地址
     * @param string $name 生成文件名(可包含在地址里)
     * @param string $ext  文件扩展名(可包含在地址或文件名里)
     */
    public function write(string $path, string $name = '', string $ext = '')
    {
        try {
            $path_info = $this->validateExportParsms($path, $name, $ext);

            // 获取Spreadsheet对象
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // 从第二行开始写入数据
            $rows = 1;
            foreach ($this->data as $item) {
                $column_key = 'A';
                foreach ($item as $value) {
                    // 单元格内容写入
                    $sheet->setCellValue($column_key.$rows, $value);
                    ++$column_key;
                }
                ++$rows;
            }

            $writer = IOFactory::createWriter($spreadsheet, ucfirst($path_info['ext']));

            $result = $writer->save($path_info['complete_path']);
        } catch (\Throwable $th) {
            throw $th;
        }

        return true;
    }

    /**
     * 读取表格内容.
     *
     * @param string $file_path 上传的文件路径
     */
    public function read(string $file_path)
    {
        try {
            // 创建读操作
            $reader = IOFactory::createReader($this->ext);

            // 打开文件 载入excel表格
            $spreadsheet = $reader->load($file_path);

            // 获取活动工作簿
            $sheet = $spreadsheet->getActiveSheet();

            // 获取内容的最大列 如 D
            $highest = Coordinate::columnIndexFromString($sheet->getHighestColumn());

            // 获取内容的最大行 如 4
            $rows = $sheet->getHighestRow();

            // 用于存储表格数据
            $data = [];
            for ($i = 1; $i <= $rows; ++$i) {
                $row = [];
                for ($j = 1; $j <= $highest; ++$j) {
                    $row[] = $sheet->getCellByColumnAndRow($j, $i)->getValue();
                }
                $data[] = $row;
            }
            $this->data = $data;
        } catch (\Throwable $th) {
            throw $th;
        }

        return $data;
    }

    /*****************************************************************************************************. */

    /**
     * 验证导出参数.
     *
     * @param string $path 生成文件保存地址
     * @param string $name 生成文件名(可包含在地址里)
     * @param string $ext  文件扩展名(可包含在地址或文件名里)
     */
    private function validateExportParsms(string $path, string $name = '', string $ext = ''): array
    {
        $path_info = pathinfo($path);
        if (mb_substr($path, -1, 1) == '/') {
            $path_info['dirname'] = rtrim($path, '/');
            $path_info['basename'] = '';
            $path_info['extension'] = '';
        }
        if (empty($path_info['basename']) && empty($name)) {
            throw new \Exception('缺少文件名');
        }
        //文件名
        $name = $path_info['basename'] ?? '' ?: $name;
        $name_info = pathinfo($name);

        //扩展名
        $ext = (($path_info['extension'] ?? '' ?: $name_info['extension'] ?? '') ?: $ext) ?: $this->ext;
        if (!in_array($ext, self::ALLOW_EXT)) {
            throw new \Exception('文件扩展名错误');
        }
        //完整地址
        $complete_path = $path_info['dirname'].'/'.$name.'.'.$ext;

        return [
            'complete_path' => $complete_path,
            'dirname' => $path_info['dirname'],
            'name' => $name,
            'ext' => $ext,
        ];
    }
}
