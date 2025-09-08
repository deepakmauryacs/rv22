class Exporter {
    constructor(options) {
        this.chunkSize = options.chunkSize || 1000;
        this.rowLimitPerSheet = options.rowLimitPerSheet || 200000;
        this.headers = options.headers || [];
        this.totalUrl = options.totalUrl;
        this.batchUrl = options.batchUrl;
        this.token = options.token || null;
        this.getParams = options.getParams || (() => ({}));
        this.exportName = options.exportName || 'Export';
        this.expButton = options.expButton || null;
        this.exportProgress = options.exportProgress || '#export-progress';
        this.progressText = options.progressText || '#progress-text';
        this.progress = options.progress || '#progress';
        this.fillterReadOnly = options.fillterReadOnly || '.fillter-form-control';
        this.cursorKeys = options.cursorKeys || [];
        this.cursor = {};

        this.sheetCount = 1;
        this.currentSheetRows = 0;
        this.currentProgress = 0;
        this.currentChunk = 0;
        this.totalChunks = 0;
        this.workbook = new ExcelJS.Workbook();
        this.currentSheet = this.workbook.addWorksheet(`Sheet${this.sheetCount}`);
        if (this.headers.length) {
            this.currentSheet.addRow(this.headers);
        }
    }

    start() {
        if (!this.expButton || $(this.expButton).is(':disabled')) return;

        $(this.expButton).attr('disabled', true);
        $(this.exportProgress).show();
        $(this.progressText).text('Progress: 0%');
        $(this.fillterReadOnly).attr('readonly', true);
        this.cursor = {};
        this.workbook = new ExcelJS.Workbook();
        this.sheetCount = 1;
        this.currentSheet = this.workbook.addWorksheet(`Sheet${this.sheetCount}`);
        if (this.headers.length) {
            this.currentSheet.addRow(this.headers);
        }
        this.currentSheetRows = 0;

        $.ajax({
            url: this.totalUrl,
            type: 'GET',
            data: {
                ...this.getParams(),
                _token: this.token
            },
            dataType: 'json',
            success: (response) => {
                this.totalChunks = Math.ceil(response.total / this.chunkSize);
                this.fetchChunk(this.currentChunk);
            },
            error: () => {
                this.showError('Error fetching total count');
            }
        });
    }

    fetchChunk(chunkIndex) {
        const payload = {
            ...this.getParams(),
            limit: this.chunkSize,
            _token: this.token
        };

        if (this.cursorKeys.length === 0) {
            payload.start = chunkIndex * this.chunkSize;
        } else {
            this.cursorKeys.forEach(key => {
                if (this.cursor[key]) {
                    payload[key] = this.cursor[key];
                }
            });
        }

        $.ajax({
            url: this.batchUrl,
            type: 'GET',
            data: payload,
            dataType: 'json',
            success: (response) => {
                const data = response.data;
                if (data.length > 0) {
                    data.forEach(row => {
                        if (this.currentSheetRows >= this.rowLimitPerSheet) {
                            this.sheetCount++;
                            this.currentSheet = this.workbook.addWorksheet(`Sheet${this.sheetCount}`);
                            if (this.headers.length) {
                                this.currentSheet.addRow(this.headers);
                            }
                            this.currentSheetRows = 0;
                        }
                        this.currentSheet.addRow(row);
                        this.currentSheetRows++;
                    });
                }

                if (this.cursorKeys.length) {
                    this.cursorKeys.forEach(key => {
                        this.cursor[key] = response[key];
                    });
                }

                this.currentProgress = Math.round(((this.currentChunk + 1) / this.totalChunks) * 100);
                $(this.progress).css('width', this.currentProgress + '%');
                $(this.progressText).text('Progress: ' + this.currentProgress + '%');
                this.currentChunk++;

                if (this.currentChunk < this.totalChunks) {
                    this.fetchChunk(this.currentChunk);
                } else {
                    this.finish();
                }
            },
            error: () => {
                this.showError('Error fetching data chunk');
            }
        });
    }

    finish() {
        if (this.workbook.worksheets.length === 0) {
            this.showError('No data to export!');
            return;
        }

        const now = new Date();
        const date = now.toLocaleDateString('en-GB').replace(/\//g, '-');
        const time = now.toLocaleTimeString('en-GB', { hour12: false }).replace(/:/g, '-');
        const fileName = `${this.exportName}-${date}-${time}.xlsx`;

        this.workbook.xlsx.writeBuffer().then(buffer => {
            const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = fileName;
            link.click();
        }).catch(() => {
            this.showError('Error generating file');
        }).finally(() => {
            $(this.exportProgress).hide();
            $(this.progress).css('width', '0%');
            $(this.fillterReadOnly).attr('readonly', false);
            $(this.expButton).attr('disabled', false);
        });
    }

    showError(message) {
        alert(message);
        $(this.exportProgress).hide();
        $(this.progressText).text('Progress: 0%');
        $(this.progress).css('width', '0%');
        $(this.fillterReadOnly).attr('readonly', false);
        $(this.expButton).attr('disabled', false);
    }
}
