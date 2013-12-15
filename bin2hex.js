// 二进制文件转换为16进制格式，类似于xxd -p xxx.bin > xxx.hex
// xxd生成的数据，会自动加上换行，比较麻烦，找了一圈，没有找到合适的
// 代码，就自己用js写了，感觉fileSteam还是很适合的。
var fs = require('fs');
var file = './tools/PSTools/PsExec.exe';
var steam = fs.createReadStream(file);
var writefile = fs.createWriteStream(file.replace(/\.exe$/, '.hex'));

writefile.write('0x');

function writeTofile(write, buf){
  var code = buf[0];
  var i = 0;
  var len = buf.length;
  var str = '';
  while(i < buf.length){
    code = code.toString(16);
    str += code.length > 1 ? code : '0' + code;
    i += 1;
    code = buf[i];
  }
  write.write(str);
  console.log('writing....');
}

steam.on('data', function(buf){
  writeTofile(writefile, buf);
});

steam.on('end', function(buf){
  writefile.end();
  console.log('success');
});
